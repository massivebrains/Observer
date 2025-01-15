<?php
	parse_str(implode('&', array_slice($argv, 1)), $_GET);
	const REQUIRED_COVERAGE = 0.7;

	$output_path = isset($_GET['output_path']) ? $_GET['output_path'] : "";
	$coverage_file = isset($_GET['coverage_path']) ? $_GET['coverage_path'] : "";
	$generate_badly_unit_classes = isset($_GET['generate_badly_unit_classes']) ? $_GET['generate_badly_unit_classes'] : false;
	$badly_united_files_path = isset($_GET['badly_united_files_path']) ? $_GET['badly_united_files_path'] : "";

	if (empty($coverage_file)) {
		throw new InvalidArgumentException('parameter coverage_path needs to be defined');
	}

	if (empty($output_path)) {
		throw new InvalidArgumentException('output output_path needs to be defined');
	}

	if (!file_exists($coverage_file)) {
		throw new InvalidArgumentException("coverage_path : {$coverage_file} does not exists");
	}

	if (!empty($badly_united_files_path) && !file_exists($badly_united_files_path) && !$generate_badly_unit_classes) {
		throw new InvalidArgumentException("badly_united_files_path: {$badly_united_files_path} does not exists");
	}

	coverage_test($coverage_file, $output_path, $generate_badly_unit_classes, $badly_united_files_path);

	/**
	 * Does coverage test by reading coverages from given coverage input file.
	 * Allows badly coverage for
	 * Exits with 1 if coverage test is not successful.
	 *
	 *
	 * @param string $coverage_path
	 * @param string $output_path
	 * @param boolean $generate_badly_unit_classes
	 * @param string $badly_united_files_path
	 */
	function coverage_test($coverage_path, $output_path, $generate_badly_unit_classes, $badly_united_files_path) {
		$coverage_by_files = get_coverage_by_file_names($coverage_path);
		$badly_united_files = get_badly_united_files($badly_united_files_path);
		$statuses = [];
		$too_low_coverage_files = [];

		foreach ($coverage_by_files as $file_name => $coverage) {
			if ($file_name === 'total') {
				continue;
			}
			if ($coverage <= REQUIRED_COVERAGE) {
				$too_low_coverage_files[] = $file_name;
			}
			if ($coverage <= REQUIRED_COVERAGE && !isset($badly_united_files[$file_name])) {
				$statuses[] = "File: " . $file_name . " has too low coverage : " . $coverage . " Required coverage: " . REQUIRED_COVERAGE;

			} else if ($coverage > REQUIRED_COVERAGE && isset($badly_united_files[$file_name])) {
				$statuses[] = "File: " . $file_name . " has too high coverage : " .
					$coverage . " and is in phpunit_badly_united_classes.txt list. remove it from there.";
			}
		}

		foreach ($badly_united_files as $file_name => $value) {
			if (!isset($coverage_by_files[$file_name])) {
				$statuses[] = "File: {$file_name} exists in {$badly_united_files_path} but does not exists in coverage. remove it from there.";
			}
		}

		if ($generate_badly_unit_classes) {
			sort($too_low_coverage_files);
			$string = implode("\n", $too_low_coverage_files);
			write_to_file($string, $badly_united_files_path);
		}

		$string = "Coverage analysis done\n";
		sort($statuses);
		$string .= implode("\n", $statuses) . "\n";

		$string .= "total_elements: " . $coverage_by_files['total']['total_elements'] . "\n";
		$string .= "total_covered_elements: " . $coverage_by_files['total']['total_covered_elements'] . "\n";
		$string .= "total_coverage: " . $coverage_by_files['total']['total_coverage'] * 100 . "\n";

		write_to_file($string, $output_path);

		if (!empty($statuses)) {
			exit(1);
		}
	}

	/**
	 * Writes to $string to file
	 *
	 * @param string $string
	 * @param string $output_path
	 */
	function write_to_file($string, $output_path) {
		$file = fopen($output_path, "w") or die("Unable to open file: " . $output_path);
		fwrite($file, $string);
	}

	/**
	 * Parses from coverage file the file names and their coverages
	 *
	 * @param string $file_path
	 * @return array
	 */
	function get_coverage_by_file_names($file_path) {
		$coverage_by_file_name = [];
		$json = parse_xml_to_json($file_path);
		$total_elements = 0;
		$total_covered_elements = 0;
		$project = $json['project'];
		if (isset($project)) {
			if (isset($project['file']['@attributes'])) {
				$coverage = get_coverage_for_file($project['file']);
				$coverage_by_file_name[$coverage['name']] = $coverage['coverage'];
				$total_elements += $coverage['element_count'];
				$total_covered_elements += $coverage['covered_element_count'];
			} else {
				foreach ($project['file'] as $file) {
					$coverage = get_coverage_for_file($file);
					$coverage_by_file_name[$coverage['name']] = $coverage['coverage'];
					$total_elements += $coverage['element_count'];
					$total_covered_elements += $coverage['covered_element_count'];
				}
			}
			if (array_key_exists('package', $project)) {
				$packages = $project['package'];
				if (isset($packages['file']) && is_array($packages['file'])) {
					foreach ($packages['file'] as $file) {
						$coverage = get_coverage_for_file($file);
						$coverage_by_file_name[$coverage['name']] = $coverage['coverage'];
						$total_elements += $coverage['element_count'];
						$total_covered_elements += $coverage['covered_element_count'];
					}
				} else {
					foreach ($packages as $package) {
						if (isset($package['file']['@attributes'])) {
							$coverage = get_coverage_for_file($package['file']);
							$coverage_by_file_name[$coverage['name']] = $coverage['coverage'];
							$total_elements += $coverage['element_count'];
							$total_covered_elements += $coverage['covered_element_count'];
						} else {
							foreach ($package['file'] as $file) {
								$coverage = get_coverage_for_file($file);
								$coverage_by_file_name[$coverage['name']] = $coverage['coverage'];
								$total_elements += $coverage['element_count'];
								$total_covered_elements += $coverage['covered_element_count'];
							}
						}
					}
				}
			}
		}

		$coverage_by_file_name['total'] = [];
		$coverage_by_file_name['total']['total_coverage'] = $total_elements > 0 ? $total_covered_elements / $total_elements : 1;
		$coverage_by_file_name['total']['total_covered_elements'] = $total_covered_elements;
		$coverage_by_file_name['total']['total_elements'] = $total_elements;

		return $coverage_by_file_name;
	}

	/**
	 * Returns coverage for single file
	 *
	 * @param array $file
	 * @return array
	 */
	function get_coverage_for_file($file) {
		$name = $file['@attributes']['name'];
		$element_count = $file['metrics']['@attributes']['elements'];
		$covered_element_count = $file['metrics']['@attributes']['coveredelements'];
		$coverage = 1;
		if ($element_count != 0) {
			$coverage = $covered_element_count / $element_count;
		}
		return [
			'name' => $name,
			'coverage' => $coverage,
			'covered_element_count' => $covered_element_count,
			'element_count' => $element_count
		];
	}

	/**
	 * Returns array of files which can have badly unit coverage
	 *
	 * @param string $badly_united_files_path
	 * @return array
	 */
	function get_badly_united_files($badly_united_files_path) {
		if (empty($badly_united_files_path) || !file_exists($badly_united_files_path)) {
			return [];
		}

		$lines = file($badly_united_files_path);
		$files = [];
		foreach ($lines as $line) {
			$trimmed_line = trim($line);
			if (empty($trimmed_line) || substr($trimmed_line, 0, 1) === "#") {
				continue;
			}

			$files[trim($line)] = 1;
		}
		return $files;
	}

	/**
	 * Parses json file to array
	 *
	 * @param string $file_path
	 * @return array json format
	 */
	function parse_xml_to_json($file_path) {
		$file_as_string = file_get_contents($file_path);
		$xml = simplexml_load_string($file_as_string, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		return json_decode($json, true);
	}
