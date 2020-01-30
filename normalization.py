import pandas as pd
import matplotlib.pyplot as plt
import numpy as np

def quantileNormalize(data, data_model, Field="FPKM"):
	
	n_ranks = 100
	
	# drop all zero values. We don't use it in normalization
	
	data_zero = data[data[Field] == 0]
	data_process = data[data[Field] > 0]
	assert len(data_zero) + len(data_process) == len(data), 'Bad data for normalization'
	
	data_model_zero = data_model[data_model[Field] == 0]
	data_model_process = data_model[data_model[Field] > 0]
	assert len(data_model_zero) + len(data_model_process) == len(data_model), 'Bad data model for normalization'
	
	# compute percentiles
	
	normalize_data_list = []
	for percentile in range(1, n_ranks + 1):
		if percentile == 1:
			data_temp = data_process[np.logical_and(data_process[Field] <= np.percentile(data_process[Field], percentile), data_process[Field] >= np.percentile(data_process[Field], percentile - 1))]
			data_model_temp = data_model_process[np.logical_and(data_model_process[Field] <= np.percentile(data_model_process[Field], percentile), data_model_process[Field] >= np.percentile(data_model_process[Field], percentile - 1))]
		else:
			data_temp = data_process[np.logical_and(data_process[Field] <= np.percentile(data_process[Field], percentile), data_process[Field] > np.percentile(data_process[Field], percentile - 1))]
			data_model_temp = data_model_process[np.logical_and(data_model_process[Field] <= np.percentile(data_model_process[Field], percentile), data_model_process[Field] > np.percentile(data_model_process[Field], percentile - 1))]
		data_perc_mean = np.mean(data_temp[Field])
		data_model_perc_mean = np.mean(data_model_temp[Field])
		data_temp[Field] = data_temp[Field].apply(lambda x: data_model_perc_mean / data_perc_mean * x)
		normalize_data_list.append(data_temp)
	
	assert len(normalize_data_list) == n_ranks
	normalize_data_list.append(data_zero)
	normalize_data = pd.concat(normalize_data_list)
	assert len(normalize_data) == len(data)
	
	return normalize_data
