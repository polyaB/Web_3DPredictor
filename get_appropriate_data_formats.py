from pybiomart import Dataset, Server
import pandas as pd
import logging
import sys
import re
from normalization import *

def get_rna_format_for_3DPredictor(RNAseq_file, output_file, genome_assembly, RNAseq_model_file):
	gene_id_field = 'gene_id'
	ch_func = lambda x: x.str.upper() if x.map(type).eq(str).all() else x
	RNAseq_data = pd.read_csv(RNAseq_file, sep="\t").apply(ch_func)
	dataset = pd.read_csv("input/ensembl_genes/"+genome_assembly+"_ensembl_genes.txt", sep="\t").apply(ch_func)
	matcher = RNAseq_data[gene_id_field].apply(lambda x: False if (re.match("^ENS.+", x) is None) else True)
	is_ensembl = (matcher[matcher == True].size / matcher.size) > 0.5
	logging.getLogger(__name__).info("Input data contains " + ("Ensembl gene IDs" if is_ensembl else "gene names or non-Ensembl gene IDs"))
	RNAseq_data["Gene_ID"] = RNAseq_data[gene_id_field].apply(lambda x: x.split(".")[0]) if is_ensembl else RNAseq_data[gene_id_field]
	merge_field = "Gene stable ID" if is_ensembl else "Gene name"
	#non_unique = []
	#another_chrom = []
	#for var in set(dataset["Gene name"].to_list()):
	#	tab = dataset[dataset["Gene name"] == var]
	#	if tab["Gene name"].size > 1: 
	#		non_unique += [var]
	#		chroms = len(set(dataset["Chromosome/scaffold name"].to_list()))
	#		if chroms > 1: another_chrom += [(var, chroms)]
	#if len(non_unique) != 0: logging.getLogger(__name__).warning("Non-unique names: " + ' '.join(non_unique))
	#if len(another_chrom) != 0: logging.getLogger(__name__).warning("Non-unique chroms: " + ' '.join(another_chrom))
	FinalData = pd.merge(left=RNAseq_data,right=dataset,how="inner", left_on="Gene_ID",right_on=merge_field, validate="1:1" if is_ensembl else None)
	FinalData.drop_duplicates(subset=["Gene_ID"], keep='first', inplace=True)
	assert ((len(FinalData) / len(RNAseq_data)) > 0.5), "Merging efficiency is less than 50%"
	if len(FinalData) != len(RNAseq_data): logging.getLogger(__name__).warning("Some data missing in Ensembl, "+str(len(RNAseq_data)-len(FinalData)) + " out of "+str(len(RNAseq_data)))
	#normalize FPKM values before
	RNA_data_model = pd.read_csv(RNAseq_model_file, sep="\t")
	FinalData = quantileNormalize(FinalData, RNA_data_model)
	FinalData[["Chromosome/scaffold name", "Gene start (bp)", "Gene end (bp)", "FPKM", "Gene name"]].to_csv(output_file, sep="\t",index=False)

if __name__ == "__main__": get_rna_format_for_3DPredictor(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4])
