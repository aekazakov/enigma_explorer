#!/usr/bin/python

'''
Import 16s rRNA sequence (fasta) into mysql database

Maintainer: Yujia Liu rainl199922@berkeley.edu
'''

import sys
import pymysql
from Bio import SeqIO

if len(sys.argv) != 2 or sys.argv[1] == '-h' or sys.argv[1] == '--help' :
    print('usage:\nimport16s.py path_to_16s_seq')
    exit(1)

# parse fasta file
try :
    records = SeqIO.parse(sys.argv[1], 'fasta')
except Exception as er:
    print(str(er))
    exit(1)

config = {
    'host': '127.0.0.1',
    'user': 'root',
    'db': 'enigma',
    'charset': 'utf8'
}
db = pymysql.connect(**config)
with db :
    cursor = db.cursor()
    try :
        db.begin()
        for rec in records :
            sqlStr = 'UPDATE `isolates` SET `16s` = "%s" WHERE `isolate_id` = "%s";'\
                % (rec.seq, rec.name)
            cursor.execute(sqlStr)
        db.commit()
    except Exception as er :
        print(str(er))
        db.rollback()
        exit(1)
    cursor.close()
