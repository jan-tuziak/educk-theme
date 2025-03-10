import pandas as pd

df = pd.read_csv('all_users.csv')

df = df[df['Has Account'] == True]

print(f"Len: {len(df.index)}")

df.to_csv('mailerlite.csv', columns=['Email','First Name','Last Name'],index=False)