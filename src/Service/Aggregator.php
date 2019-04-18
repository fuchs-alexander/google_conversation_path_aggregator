<?php

namespace Colibo\GoogleConversationPathAggregator\Service;

class Aggregator
{
	private $data = [];
	private $ignoreOrderIds = [];

	public function aggregate(array $importData)
	{
		$this->data = [];
		$totalRevenue = 0;
		$doubleOrder = ['count' => 0, 'revenue' => 0];
		foreach ($importData as $data) {
			if (isset($this->ignoreOrderIds[$data['order_id']])) {
				$doubleOrder['count']++;
				$doubleOrder['revenue'] += $data['revenue'];
				//continue;
			}

			$this->ignoreOrderIds[$data['order_id']] = true;


			foreach ($data['paths'] as $key => $path) {
				if ($path == '(direct) / (none)') {
					unset($data['paths'][$key]);
				}
			}

			$data['paths'] = array_values($data['paths']);

			$totalRevenue += $data['revenue'];
			if (count($data['paths']) == 0) {
				$this->add('(direct) / (none)', $data['revenue'], 1);
			} elseif (count($data['paths']) == 1) {
				$this->add($data['paths'][0], $data['revenue'], 1);
			} elseif (count($data['paths']) == 2) {
				foreach ($data['paths'] as $path) {
					$this->add($path, $data['revenue'] * 0.5, 1 * 0.5);
				}
			} else {
				$middleFactor = 30 / (count($data['paths']) - 2) / 100;
				foreach ($data['paths'] as $key => $path) {
					if ($key == 0 || $key == count($data['paths']) - 1) {
						$this->add($path, $data['revenue'] * 0.35, 1 * 0.35);
					} else {
						$this->add($path, $data['revenue'] * $middleFactor, 1 * $middleFactor);
					}
				}
			}
		}

		$totalCount = count($importData);
		foreach ($this->data as $key => $data) {
			$this->data[$key]['revenue_percentage'] = round(($data['revenue'] / $totalRevenue) * 100, 2);
			$this->data[$key]['count_percentage'] = round(($data['count'] / $totalCount) * 100, 2);
			$this->data[$key]['revenue'] = round($data['revenue'], 2);
		}

		return $this->data;
	}

	private function add($path, $revenue, $count)
	{
		if (!isset($this->data[$path])) {
			$this->data[$path] = [
				'revenue' => 0,
				'count' => 0
			];
		}

		$this->data[$path]['revenue'] += $revenue;
		$this->data[$path]['count'] += $count;
	}
}