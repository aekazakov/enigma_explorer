import csv
from isolates.models import Isolate
in_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/isolates.csv'

unique_names = set()
with open(in_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    for row in csvreader:
        if row[0] == 'id':
            continue
        if row[1] in unique_names:
            print(row[1], 'in line', row[0], 'already was loaded')
            continue
        else:
            unique_names.add(row[1])
        isolate = Isolate(isolate_id = row[1],
                             condition = row[2],
                             order = row[3],
                             closest_relative = row[4],
                             similarity = float(row[5]),
                             date_sampled = row[6],
                             sample_id = row[7],
                             lab = row[8],
                             campaign = row[9],
                             rrna = row[10]
                          )
        isolate.save()
