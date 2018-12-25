#!/usr/bin/python

'''
Maintainer Yujia Liu rainl199922@berkeley.edu
Fetch genome from NCBI by species name
'''

import sys, re, json
from Bio import Entrez, SeqIO

def main() :
    ######################
    # Handle parameters
    ######################

    if len(sys.argv) == 1 or sys.argv[1] not in ['-s', '--search', '-i', '--id']  :
        print('''\
Usage:
fetchGenome.py [OPTION] [strain info]
Either fetch all genome id by specific strain or get genome sequence by id.
    -s, --search species [strain]       Fetch all genome id
    -i, --id species id                 Fetch sequence by id
''')
        exit(1)
    else :
        if sys.argv[1] in ['-s', '--search'] and len(sys.argv) == 3 :
            genomeList, genomeStrainList = fetchG(sys.argv[2])
            res = {'id': genomeList, 'strain': genomeStrainList}
            print(json.dumps(res))
        elif sys.argv[1] in ['-s', '--search'] and len(sys.argv) == 4 :
            genomeList, genomeStrainList = fetchG(sys.argv[2], sys.argv[3])
            res = {'id': genomeList, 'strain': genomeStrainList}
            print(json.dumps(res))
        elif sys.argv[1] in ['-i', '--id'] :
            print(fetchS(sys.argv[2]))

def fetchG(speciesName, strain="") :
    ##########################
    # Search for a list of related genomes
    ##########################
    
    Entrez.email = EMAIL
    searchRet = Entrez.esearch(db='genome', term=speciesName+'[orgn]')
    searchRet = Entrez.read(searchRet)
    try :
        linkId = searchRet['IdList'][0]
    except IndexError :
        return "", ""
    linkList = Entrez.elink(dbfrom='genome', db='nuccore', id=linkId, term="gene+in+chromosome[prop]")
    genomeList = Entrez.read(linkList)[0]['LinkSetDb'][0]['Link']
    genomeList = [entry['Id'] for entry in genomeList]

    ########################
    # Get a all genome tags
    ########################

    genomeSpeciesList = []
    genomeIdList = []
    for entry in genomeList :
        summary = Entrez.esummary(db='nuccore', id=entry)
        genomeTitle = Entrez.read(summary)[0]['Title']
        genomeSpecies = ""
        for segment in re.split(', | ', genomeTitle) :
            if segment in ['whole', 'complete', 'genome', 'chromosome'] :
                break
            else :
                if len(genomeSpecies) == 0 :
                    genomeSpecies = segment
                else :
                    genomeSpecies = genomeSpecies + ' ' + segment
        if genomeSpecies in genomeSpeciesList :
            continue
        # if genome matches specific strain, insert this entry to the head
        if len(strain) > 0 and len(re.findall(strain, genomeSpecies, re.IGNORECASE)) > 0 :
            genomeSpeciesList.insert(0, genomeSpecies)
            genomeIdList.insert(0, entry)
        else :
            genomeSpeciesList.append(genomeSpecies)
            genomeIdList.append(entry)
        # Considering the delay, trancate top 5 if the list is long!
        if (len(genomeIdList) >= MAX_GENOME_LIST) :
            break

    return genomeIdList, genomeSpeciesList

def fetchS(genomeId) :
    Entrez.email = EMAIL
    genomeSeq = Entrez.efetch(db='nuccore', id=genomeId, rettype='fasta', retmode='text').read()
    return genomeSeq

EMAIL = 'rainl199922@berkeley.edu'
MAX_GENOME_LIST = 12
main()
