<?php

namespace Colibo\GoogleConversationPathAggregator\Service;

use \Colibo\GoogleConversationPathAggregator\GoogleAnalyticsWrapper;

class Importer
{
	private $googleAnalyticsWrapper;

	public function __construct(GoogleAnalyticsWrapper $googleAnalyticsWrapper)
	{
		$this->googleAnalyticsWrapper = $googleAnalyticsWrapper;
	}

	public function fetchByDateRange(\DateTime $startDate, \DateTime $endDate)
	{
		$result = $this->googleAnalyticsWrapper->getAnalyticsService()->data_mcf->get(
			$this->googleAnalyticsWrapper->getAnalyticsId(),
			$startDate->format('Y-m-d'),
			$endDate->format('Y-m-d'),
			'mcf:totalConversionValue',
			['dimensions' => 'mcf:sourceMediumPath, mcf:transactionId', 'filters' => 'mcf:totalConversionValue>0', 'max-results' => 4000]
		)->getRows();

		$data = [];
		foreach ($result as $row) {
			$modelData = $row['modelData'];

			$conversionPathValues = $this->getModelDataByName('conversionPathValue', $modelData);

			$fullConversionPath = implode(' # ', array_map(function ($item) {
				return $item['nodeValue'];
			}, $conversionPathValues));

			$paths = [];
			foreach ($conversionPathValues as $path) {
				$paths[] = $path['nodeValue'];
			}

			$d = [
				'paths' => $paths,
				'order_id' => $modelData[1]['primitiveValue'],
				'revenue' => $modelData[2]['primitiveValue'],
				'full_path' => $fullConversionPath
			];

			$data[] = $d;
			//echo $fullConversionPath . PHP_EOL;

			//print_r($modelData);
		}

		return $data;
	}

	private function getModelDataByName($name, $modelData)
	{
		foreach ($modelData as $data) {
			if (isset($data[$name]))
			{
				return $data[$name];
			}
		}
		return [];
	}

}
