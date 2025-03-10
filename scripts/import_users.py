import pandas as pd
import numpy as np

df = pd.read_csv('users_to_import.csv', dtype={'Billing Zip': 'str'})
df = df.reset_index() 

course_ids = {
    "V-Ray for SketchUp Visualization Course" : 911,
    "Free Course - V-Ray for SketchUp Visualization" : 646,
    "Visualizations for Beginners. V-Ray for SketchUp Course" : 806,
    "V-Ray for SketchUp Advanced Visualization Course" : 678
}

def assign_meta_field(f, data, meta_field):
    if data is not np.nan:
        f.write(f"php8 /bin/wp user meta add $USER_ID {meta_field} \"{data}\"\n")

f = open("import_users.sh", "w")
f.write("#!/bin/bash" + "\n")
f.write("TIMESTAMP=\"$(date +%s)\"" + "\n")
f.close()
f = open("import_users.sh", "a")
num_of_rows = len(df.index)
for index, row in df.iterrows():
    if row['Has Account'] is False:
        continue
    
    f.write(f"echo \"Adding user {index+1} out of {num_of_rows}: {row['Email']}\"\n")
    f.write(f"USER_ID=\"$(php8 /bin/wp user create {row['First Name'].replace(" ", "")}.{row['Last Name'].replace(" ", "")} {row['Email']} --porcelain)\"" + "\n")
    assign_meta_field(f, row['First Name'], "first_name")
    assign_meta_field(f, row['Last Name'], "last_name")

    for course_name, course_id in course_ids.items():
        if row['Member Areas'] is not np.nan and course_name in row['Member Areas']:
            f.write(f"php8 /bin/wp user meta add $USER_ID course_{course_id}_access_from $TIMESTAMP\n")
            f.write(f"php8 /bin/wp user meta add $USER_ID learndash_course_{course_id}_enrolled_at $TIMESTAMP\n")
    
    if row['Member Areas'] is not np.nan:
        f.write(f"php8 /bin/wp user meta add $USER_ID course_points 0\n")
    
    if row['Order Count'] == 0:
        continue
        
    f.write(f"php8 /bin/wp user meta add $USER_ID paying_customer \"1\"\n")

    assign_meta_field(f, row['First Name'], "billing_first_name")
    assign_meta_field(f, row['Last Name'], "billing_last_name")
    assign_meta_field(f, row['Billing Zip'], "billing_postcode")
    assign_meta_field(f, row['Billing Country'], "billing_country")
    assign_meta_field(f, row['Email'], "billing_email")

    f.write(f"php8 /bin/wp user meta add $USER_ID shipping_method \"\"\n")
    f.write(f"php8 /bin/wp user meta add $USER_ID billing_tax_no \"\"\n")
    f.write(f"php8 /bin/wp user meta add $USER_ID billing_company_name \"\"\n")

f.write("php8 /bin/wp cache flush\n")
f.close()