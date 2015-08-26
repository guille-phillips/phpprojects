<?php

	class BetEasyFeed {
		public function ProcessFeed() {
			$data_json = $this->GetFeed();
			$this->StoreData($this->ExtractData($data_json));
		}

		private function GetFeed() {
			$json = Curl('https://api.beteasy.com.au/Event/QueryMasterEventsByClassAndOrderBy?OnlyIsOpenForBetting=true&DateFrom=2015-03-04T04%3A55%3A00.0000000%2B00%3A00&DateTo=2016-03-04T12%3A59%3A00.0000000%2B00%3A00&MasterEventTypeID=2&EventTypes=101&MasterCategoryID=16&CategoryClassOrderBys=0-99&EventClassOrderByMode=1&EventClassOrderByValue=1&RowsPerPage=50&CurrentPage=0');

			if (($data = json_decode($json))===false) {
				// do nothing
			} else {
				//var_dump($data->Results[0]);
			}

			return $data->Results[0];
		}

		private function ExtractData($data_json) {
			// pull out interesting bits
			$extracted = array();
			return $extracted;
		}

		private function StoreData($extracted) {
			// store the extracted feed data in database

		}

	}