#!/bin/python

import sys, json, subprocess, os

# check cmdline parameters
if len(sys.argv) != 3 or sys.argv[1] == '-h' or sys.argv[1] == '--help' :
    print('Usage: localBlast.py blast-db nt-sequence-or-file')
    exit()

# Acquire the list of local blastdb
dbPaths = os.environ['BLASTDB']
dbPath = dbPaths.split(';')[0]    # in case for multiple paths, choose only 1st
listCmd = 'blastdbcmd -list "%s" -recursive' % (dbPath)
listProc = subprocess.Popen(listCmd, stdout=subprocess.PIPE, shell=True)
listOut, _ = listProc.communicate()
# the output is like $BLASTDB/mydb nucleotide
dbSet = []
for line in listOut.decode('utf-8').split('\n') :
    dbSet.append(line.split('/')[-1].split(' ')[0])

# Run blast commands
db, seq = sys.argv[1], sys.argv[2]
# output fmt 15: single file JSON
# check is file or seq
if len(seq) < 128 and os.path.isfile(seq) :
    blastCmd = 'blastn -db %s -query "%s" -outfmt 15' % (db, seq)    #in case isoid contains space
else :
    blastCmd = 'echo "%s" | blastn -db %s -outfmt 15' % (seq, db)
if db not in dbSet :
    blastCmd += ' -remote'
    # hint to stderr in order not to tangle with output
    print("Local BLAST DB not found. Try remote", file=sys.stderr)
else :
    print("Local BLAST DB found.", file=sys.stderr)

pro = subprocess.Popen(blastCmd, stdout=subprocess.PIPE, shell=True)
blastOut, _ = pro.communicate()    #communicate() blocks the process

#parse json
blastObj = json.loads(blastOut)['BlastOutput2'][0]['report']    #unwrap the 1st layer
MAX_HIT = 50
blastRet = blastObj['results']['search']['hits']
queryLen = float(blastObj['results']['search']['query_len'])
myout = []
for i, hit in enumerate(blastRet) :
    # The first hit is itself
    if i == 0 :
        continue
    # upperbound
    if i > MAX_HIT :
        break
    myobj = { \
            'isoid': hit['description'][0]['id'], \
            'title': hit['description'][0]['title'], \
            'coverage': hit['hsps'][0]['align_len'] / max(queryLen, hit['hsps'][0]['align_len']), \
            'identity': hit['hsps'][0]['identity'] / queryLen, \
            'start': hit['hsps'][0]['query_from'], \
            'end': hit['hsps'][0]['query_to'], \
            'align': { \
                'qseq': hit['hsps'][0]['qseq'], \
                'hseq': hit['hsps'][0]['hseq'], \
                'midline': hit['hsps'][0]['midline']
            }, \
            'evalue': hit['hsps'][0]['evalue'] \
            }
    myout.append(myobj)
print(json.dumps(myout))
