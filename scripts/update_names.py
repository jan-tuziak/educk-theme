import pandas as pd
import numpy as np

df = pd.read_csv('all_users.csv', encoding="utf-8", dtype={'Billing Zip': 'str'})
df = df.reset_index() 

f = open("update_names.sh", "w")
f.write("#!/bin/bash" + "\n")
f.close()
f = open("update_names.sh", "a", encoding="utf-8")
num_of_rows = len(df.index)
for index, row in df.iterrows():
    if row['Has Account'] is False:
        continue
    
    f.write(f"echo \"Updating user {index+1} out of {num_of_rows}: {row['Email']}\"\n")
    f.write(f"php8 /bin/wp user update {row['Email']} --first_name=\"{row['First Name']}\" --last_name=\"{row['Last Name']}\"\n")

f.write("php8 /bin/wp cache flush\n")
f.close()