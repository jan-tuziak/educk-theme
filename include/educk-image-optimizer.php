<?php
/**
 * Educk Image Optimizer (upload-time)
 *
 * How to use:
 * 1) Save this file as: wp-content/mu-plugins/educk-image-optimizer.php  (recommended)
 *    OR anywhere in your theme and include it from functions.php
 * 2) It will automatically resize + convert JPG/PNG uploads to AVIF (if supported) or WebP.
 *
 * Notes:
 * - Keeps originals in /uploads/educk-originals/ by default (can disable).
 * - Designed to work with Media Library + Elementor uploads.
 */

if (!defined('ABSPATH')) exit;

class Educk_Image_Optimizer_Upload {

    // ======= CONFIG =======
    private const MAX_WIDTH      = 2560; // px
    private const MAX_HEIGHT     = 2560; // px
    private const WEBP_QUALITY   = 80;   // 1-100
    private const AVIF_QUALITY   = 45;   // 1-100 (some servers ignore; still fine)
    private const KEEP_ORIGINALS = true; // store original in uploads/educk-originals/
    // ======================

    public static function init(): void {
        // Runs after upload is handled, before attachment metadata generation.
        add_filter('wp_handle_upload', [__CLASS__, 'optimize_on_upload'], 20, 2);
    }

    /**
     * @param array $upload  Array: file, url, type
     * @param string $context Context ("upload" etc.) – passed by WP
     */
    public static function optimize_on_upload(array $upload, string $context): array {
        if (empty($upload['file']) || empty($upload['type'])) return $upload;

        $file = $upload['file'];
        $mime = $upload['type'];

        // Only process JPG/PNG.
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) return $upload;

        // Skip if it's already modern.
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['webp', 'avif'], true)) return $upload;

        // Ensure file exists
        if (!file_exists($file) || !is_readable($file)) return $upload;

        // WordPress editor (Imagick/GD)
        $editor = wp_get_image_editor($file);
        if (is_wp_error($editor)) return $upload;

        // Backup original before modification (optional)
        if (self::KEEP_ORIGINALS) {
            self::backup_original($file);
        }

        // Resize if needed (keeps aspect ratio)
        $size = $editor->get_size();
        if (!empty($size['width']) && !empty($size['height'])) {
            if ($size['width'] > self::MAX_WIDTH || $size['height'] > self::MAX_HEIGHT) {
                $editor->resize(self::MAX_WIDTH, self::MAX_HEIGHT, false);
            }
        }

        // Prefer AVIF if supported, else WebP
        $target_mime = self::supports_mime($editor, 'image/avif') ? 'image/avif' : 'image/webp';
        $target_ext  = ($target_mime === 'image/avif') ? 'avif' : 'webp';

        // Set quality (best effort; varies by editor backend)
        $quality = ($target_mime === 'image/avif') ? self::AVIF_QUALITY : self::WEBP_QUALITY;
        if (method_exists($editor, 'set_quality')) {
            $editor->set_quality($quality);
        }

        // New file path, same basename, new extension
        // IMPORTANT: WordPress makes the *original* upload filename unique (e.g. .jpg),
        // but after converting to .avif/.webp we must ensure uniqueness again.
        $dir      = pathinfo($file, PATHINFO_DIRNAME);
        $basename = pathinfo($file, PATHINFO_FILENAME);

        // Create a unique target filename in the same directory
        $target_filename = wp_unique_filename($dir, $basename . '.' . $target_ext);
        $new_file        = trailingslashit($dir) . $target_filename;
        if (empty($target_filename) || empty($new_file)) return $upload;

        // Save optimized file
        $saved = $editor->save($new_file, $target_mime);
        if (is_wp_error($saved) || empty($saved['path']) || !file_exists($saved['path'])) {
            return $upload;
        }

        // Remove original (we kept backup if enabled)
        @unlink($file);

        // Update the upload array so WP continues with the optimized file
        $upload['file'] = $saved['path'];

        // Update URL to match the unique filename we saved
        $upload_dir_url  = trailingslashit(dirname($upload['url']));
        $upload['url']   = $upload_dir_url . $target_filename;

        $upload['type'] = $target_mime;

        return $upload;
    }

    private static function supports_mime($editor, string $mime): bool {
        // Some editor implementations support this directly
        if (method_exists($editor, 'supports_mime_type')) {
            return (bool) $editor->supports_mime_type($mime);
        }

        // Fallback: attempt saving a tiny temp file
        $tmp = wp_tempnam('educk-img');
        if (!$tmp) return false;

        $tmp_path = $tmp . (($mime === 'image/avif') ? '.avif' : '.webp');
        $res = $editor->save($tmp_path, $mime);

        if (!is_wp_error($res) && !empty($res['path']) && file_exists($res['path'])) {
            @unlink($res['path']);
            @unlink($tmp);
            return true;
        }

        @unlink($tmp);
        return false;
    }

    private static function backup_original(string $file): void {
        $uploads = wp_get_upload_dir();
        $base    = $uploads['basedir'];

        $backup_dir = trailingslashit($base) . 'educk-originals';
        if (!is_dir($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        $filename = basename($file);
        $dest = trailingslashit($backup_dir) . $filename;

        // Avoid overwrite
        if (file_exists($dest)) {
            $dest = trailingslashit($backup_dir) . time() . '-' . $filename;
        }

        @copy($file, $dest);
    }
}

Educk_Image_Optimizer_Upload::init();

// ===============================
// WP-CLI: Bulk optimize existing images
// Usage examples:
//   wp educk optimize-images --dry-run
//   wp educk optimize-images --limit=50
//   wp educk optimize-images --offset=0 --limit=200
//   wp educk optimize-images --only-mime=image/jpeg
// ===============================
if (defined('WP_CLI') && WP_CLI) {

    /**
     * Bulk optimize existing Media Library images using the same rules as upload-time optimizer.
     */
    WP_CLI::add_command('educk optimize-images', function($args, $assoc_args) {
        $dry_run   = isset($assoc_args['dry-run']);
        $limit     = isset($assoc_args['limit']) ? max(1, (int)$assoc_args['limit']) : 0;
        $offset    = isset($assoc_args['offset']) ? max(0, (int)$assoc_args['offset']) : 0;
        $only_mime = isset($assoc_args['only-mime']) ? (string)$assoc_args['only-mime'] : '';

        $allowed_mimes = ['image/jpeg', 'image/png'];
        if ($only_mime !== '') {
            $allowed_mimes = [$only_mime];
        }

        $query_args = [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => ($limit > 0 ? $limit : -1),
            'offset'         => $offset,
            'fields'         => 'ids',
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'post_mime_type' => $allowed_mimes,
        ];

        $ids = get_posts($query_args);
        if (empty($ids)) {
            WP_CLI::success('No matching attachments found.');
            return;
        }

        WP_CLI::log(sprintf('Found %d attachments to process%s.', count($ids), $dry_run ? ' (dry-run)' : ''));

        $processed = 0;
        $skipped   = 0;
        $failed    = 0;

        $bytes_before_total = 0;
        $bytes_after_total  = 0;

        // Count of reference updates performed during this run (best-effort)
        $ref_updates_total = 0;

        /**
         * Replace old image references with new ones in common places:
         * - wp_posts.post_content
         * - Elementor JSON/meta fields
         * - wp_options.option_value (best-effort)
         *
         * We update both absolute URLs and relative /wp-content/uploads paths.
         */
        $replace_refs = function(string $old_rel, string $new_rel, array $uploads) use (&$ref_updates_total) {
            global $wpdb;

            $baseurl = trailingslashit($uploads['baseurl']);

            // e.g. /wp-content/uploads
            $uploads_path = wp_parse_url($baseurl, PHP_URL_PATH);
            $uploads_path = $uploads_path ? rtrim($uploads_path, '/') : '';

            $old_rel_norm = str_replace(DIRECTORY_SEPARATOR, '/', ltrim($old_rel, '/'));
            $new_rel_norm = str_replace(DIRECTORY_SEPARATOR, '/', ltrim($new_rel, '/'));

            $old_url  = $baseurl . $old_rel_norm;
            $new_url  = $baseurl . $new_rel_norm;

            $old_path = ($uploads_path !== '' ? $uploads_path . '/' : '/') . $old_rel_norm;
            $new_path = ($uploads_path !== '' ? $uploads_path . '/' : '/') . $new_rel_norm;

            $log_one = function(string $label, int $affected) use (&$ref_updates_total) {
                if ($affected > 0) {
                    $ref_updates_total += $affected;
                    WP_CLI::log(sprintf('  refs: %s updated rows=%d', $label, $affected));
                }
            };

            // 1) post_content
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
                $old_url, $new_url, '%' . $wpdb->esc_like($old_url) . '%'
            ));
            $log_one('posts.content (abs url)', (int) $wpdb->rows_affected);

            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
                $old_path, $new_path, '%' . $wpdb->esc_like($old_path) . '%'
            ));
            $log_one('posts.content (rel path)', (int) $wpdb->rows_affected);

            // 2) Elementor meta (JSON strings; safe for REPLACE)
            $elementor_keys = ['_elementor_data', '_elementor_page_settings', '_elementor_css'];
            foreach ($elementor_keys as $k) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key = %s AND meta_value LIKE %s",
                    $old_url, $new_url, $k, '%' . $wpdb->esc_like($old_url) . '%'
                ));
                $log_one("postmeta {$k} (abs url)", (int) $wpdb->rows_affected);

                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key = %s AND meta_value LIKE %s",
                    $old_path, $new_path, $k, '%' . $wpdb->esc_like($old_path) . '%'
                ));
                $log_one("postmeta {$k} (rel path)", (int) $wpdb->rows_affected);
            }

            // 3) options (best-effort)
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_value LIKE %s",
                $old_url, $new_url, '%' . $wpdb->esc_like($old_url) . '%'
            ));
            $log_one('options (abs url)', (int) $wpdb->rows_affected);

            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_value LIKE %s",
                $old_path, $new_path, '%' . $wpdb->esc_like($old_path) . '%'
            ));
            $log_one('options (rel path)', (int) $wpdb->rows_affected);

            // Extra: show what we searched/replaced when something changed
            if ($ref_updates_total > 0) {
                WP_CLI::log('  refs: replaced:');
                WP_CLI::log('    - ' . $old_url . '  ->  ' . $new_url);
                WP_CLI::log('    - ' . $old_path . '  ->  ' . $new_path);
            }
        };

        foreach ($ids as $attachment_id) {
            $file = get_attached_file($attachment_id);
            if (!$file || !file_exists($file)) {
                WP_CLI::warning("#{$attachment_id} missing file on disk, skipping");
                $skipped++;
                continue;
            }

            $mime = get_post_mime_type($attachment_id);
            if (!in_array($mime, ['image/jpeg', 'image/png'], true) && $only_mime === '') {
                $skipped++;
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['avif', 'webp'], true)) {
                // Already optimized
                $skipped++;
                continue;
            }

            // Prepare a fake $upload array for reuse of the upload optimizer.
            $uploads  = wp_get_upload_dir();
            $basedir  = trailingslashit($uploads['basedir']);
            $baseurl  = trailingslashit($uploads['baseurl']);
            $rel_path = ltrim(str_replace($basedir, '', $file), '/');
            $url      = $baseurl . str_replace(DIRECTORY_SEPARATOR, '/', $rel_path);

            WP_CLI::log(sprintf('Processing #%d: %s', $attachment_id, $rel_path));

            $before_bytes = @filesize($file);
            if ($before_bytes !== false) {
                $bytes_before_total += (int) $before_bytes;
            }

            if ($dry_run) {
                $processed++;
                continue;
            }

            $before_rel = $rel_path;

            $result = Educk_Image_Optimizer_Upload::optimize_on_upload([
                'file' => $file,
                'url'  => $url,
                'type' => $mime,
            ], 'educk-cli');

            if (empty($result['file']) || empty($result['type']) || !file_exists($result['file'])) {
                WP_CLI::warning("#{$attachment_id} optimization failed, skipping");
                $failed++;
                continue;
            }

            // Update attachment meta to point to the new optimized file.
            $new_file = $result['file'];
            $new_mime = $result['type'];

            $after_bytes = @filesize($new_file);
            if ($after_bytes !== false) {
                $bytes_after_total += (int) $after_bytes;
            }

            $new_rel = ltrim(str_replace($basedir, '', $new_file), '/');

            update_attached_file($attachment_id, $new_file);
            wp_update_post([
                'ID'             => $attachment_id,
                'post_mime_type' => $new_mime,
            ]);

            // Regenerate metadata / intermediate sizes from the new optimized original.
            $meta = wp_generate_attachment_metadata($attachment_id, $new_file);
            if (is_wp_error($meta) || empty($meta)) {
                WP_CLI::warning("#{$attachment_id} metadata regeneration failed");
                $failed++;
                continue;
            }
            wp_update_attachment_metadata($attachment_id, $meta);

            WP_CLI::success(sprintf('Updated #%d: %s -> %s', $attachment_id, $before_rel, $new_rel));

            // Best-effort: update hardcoded references (Elementor widgets, galleries, custom HTML)
            // from old .jpg/.png to the new .avif/.webp.
            WP_CLI::log('  refs: scanning for hardcoded URLs/paths to update...');
            $replace_refs($before_rel, $new_rel, $uploads);

            $processed++;
        }

        $saved_bytes = max(0, $bytes_before_total - $bytes_after_total);
        $saved_mb    = $saved_bytes / 1024 / 1024;
        $before_mb   = $bytes_before_total / 1024 / 1024;
        $after_mb    = $bytes_after_total / 1024 / 1024;

        WP_CLI::success(sprintf(
            'Done. processed=%d skipped=%d failed=%d | size: %.2f MB -> %.2f MB | saved: %.2f MB | ref-updates=%d',
            $processed,
            $skipped,
            $failed,
            $before_mb,
            $after_mb,
            $saved_mb,
            $ref_updates_total
        ));
    });
}
