#!/usr/bin/python
# -*- coding: utf-8 -*-

"""
localBlast.py

Author: Yujia Liu rainl199922@berkeley.edu

The script performs nucleotide commandline blast against custom database (be it local or remote) and returns JSON. Input can be a sequence or a file.

Environment Variables:
    BLASTDB: the path of blast db. If not set, all sequences will be blast with remote db
    BLASTPATH: the path of blast binaries. If not set, the script uses system $PATH
"""

from __future__ import print_function
import sys, json, subprocess, os

# constants
MAX_HIT = 50
E_THRESHOLD = 1e-10

# check cmdline parameters
if len(sys.argv) != 3 or sys.argv[1] == '-h' or sys.argv[1] == '--help' :
    print('Usage: localBlast.py blast-db nt-sequence-or-file')
    exit()

# Use blast binary path if assigned
cmdPrefix = os.getenv('BLASTPATH', '')
if cmdPrefix != '' :
    cmdPrefix += '/'

# Acquire the list of local blastdb
dbPaths = os.getenv('BLASTDB', '')
dbPath = dbPaths.strip().split(';')[0]    # in case for multiple paths, choose only 1st
listCmd = cmdPrefix + 'blastdbcmd -list "%s" -recursive' % (dbPath)
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
    blastCmd = '%sblastn -db %s -query "%s" -outfmt 15' % (cmdPrefix, db, seq)    #in case isoid contains space
else :
    blastCmd = 'echo "%s" | %sblastn -db %s -outfmt 15' % (seq, cmdPrefix, db)
# Judge whether DB is remote
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
    # filter according to evalue
    if float(hit['hsps'][0]['evalue']) >= E_THRESHOLD :
        continue
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
