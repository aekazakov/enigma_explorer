#!/bin/python

fi = open("190515_ENIGMA_Isolates_16S_sequences.fasta.aligned")
fi2 = open("190515_ENIGMA_Isolates.csv")
num = 0
set16s = []
for line in fi :
    if line[0] == '>' :
        num += 1
        set16s.append(line[1:-1])
print('Number of 16s', num)
lines = fi2.readlines()[1:]
print('Number of isolates', len(lines))
setisos = []
for line in lines :
    setisos.append(line.split('\t')[0])
print('Number of non-redundent 16s', len(set(set16s)))
print('Number of non-redundent isolates', len(set(setisos)))
red16s = []
for ele in set16s :
    if set16s.count(ele) > 1 :
        red16s.append(ele)
print('Redundent 16s', red16s)
notIn16s = [ ele for ele in setisos if ele not in set16s ]
notInIsos = [ ele for ele in set16s if ele not in setisos ]
print('Number not in 16s', len(notIn16s))
print(', '.join(notIn16s))
print('Number not in isolates', len(notInIsos))
print(', '.join(notInIsos))
