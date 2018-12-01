#!/usr/bin/python

'''
Import raw data (tsv file) into mysql database
The tsv file should have the following comumns in order:
isolate id, isolation conditions, pylogenetic order, closest relative,
similarity, date sampled, well/sample id, lab isolated, campaign/set

Maintainer: Yujia Liu rainl199922@berkeley.edu
'''

import sys
import re
import pymysql

if len(sys.argv) != 2 or sys.argv[1] == '-h' or sys.argv[1] == '--help' :
    print('usage:\nimportRaw.py path_to_raw_data')
    exit(1)

try :
    fr = open(sys.argv[1], 'r')
except Exception as er :
    print(str(er))
    exit(1)
with fr :
    config = {
        'host': '127.0.0.1',
        'user': 'root',
        'password': '',
        'db': 'enigma',
        'charset': 'utf8'
    }
    db = pymysql.connect(**config)
    with db :
        cursor = db.cursor()
        try :
            db.begin()
            for line in fr.readlines()[1:] :
                linfo = line.split('\t')
                linfo = [s.strip('"') for s in linfo]
                linfo[4] = re.findall("[0-9\.]+", linfo[4])[0]
                sqlStr = '''\
INSERT INTO `isolates` (`isolate_id`,`condition`,`phylogeny`,\
`closest_relative`,`similarity`,`date_sampled`,`sample_id`,\
`lab`,`campaign`) VALUES ("%s","%s","%s","%s",%s,"%s","%s","%s","%s");\
''' % tuple(linfo)
                cursor.execute(sqlStr)
            db.commit()
        except Exception as er:
            print(str(er))
            db.rollback()
            exit(1)
        cursor.close()
