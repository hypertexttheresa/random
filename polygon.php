<?php
class RandomPolygonGenerator {

	private $amountOfVertices;
	
	private $vertexStartCoordinates;
	
	private $optionalVertexStartCoordinates;

/**
* Random Polygon Generator class
* 
*/
	function __construct (int $stepSize = 5) {
		$stepsPerSide = 100 / $stepSize;

		$this->vertexStartCoordinates = [
			0 => [0, 0],
			$stepsPerSide => [0, 100],
			2 * $stepsPerSide => [100, 100],
			3 * $stepsPerSide => [100, 0]
		];
		$this->optionalVertexStartCoordinates = $this->getOptionalVertexStartCoordinates($stepSize, $stepsPerSide);
	}
	
/**
* Generate a css clip-path string of polygon coordinates that has 4 corners and a randomized amount of extra vertices.
* 
* @return string
*/
	public function getRandomPolygon(int $meanAmountOfVertices = 5) {
		$maximum = count($this->optionalVertexStartCoordinates);
		$this->amountOfVertices = $this->getPoissonLikeRandomAmount($meanAmountOfVertices, $maximum) + count($this->vertexStartCoordinates);
		
		$polygonCoordinates = [];
		$polygonCoordinates = array_merge($polygonCoordinates, $this->getStartVertices());
		foreach($polygonCoordinates as $index => $polygonVertex) {
			$polygonCoordinates[$index] = $this->transformVertex($polygonVertex);
		}
		
		return implode(',', $polygonCoordinates);
	}
	
/**
* Generate the vertices to create the polygon. First create the four corners, then randomly choose extra corners from the optional start coordinates
* 
* @return array
*/
	private function getStartVertices() {
		$startVertices = $this->vertexStartCoordinates;
		$amountOfOptionalVertices = $this->amountOfVertices - count($this->vertexStartCoordinates);
		for($i = 0; $i <= $amountOfOptionalVertices - 1; $i++) {
			$randomIndex = array_rand($this->optionalVertexStartCoordinates);
			$startVertices[$randomIndex] = $this->optionalVertexStartCoordinates[$randomIndex];
			unset($this->optionalVertexStartCoordinates[$randomIndex]);
		}
		
		ksort($startVertices);

		return $startVertices;
	}
	
/**
* Transform any vertex, first into a randomly chosen offset position and then into a string formatted for css clip-path
* 
* @param array $vertexCoordinates A set of coordinates
* @return string
*/
	private function transformVertex(array $vertexCoordinates) {
		$vertexCoordinates = $this->randomizeCoordinates($vertexCoordinates);
		$vertexCoordinates = $this->transformToPercentages($vertexCoordinates);
		$vertexCoordinates = implode (' ', $vertexCoordinates);
		
		return $vertexCoordinates;
	}
	
/**
* Transform a set coordinates according to the following chosen randomization: a maximum of 5 * 1% offset
* 
* @param array $coordinates A set of coordinates
* @return array
*/
	private function randomizeCoordinates(array $coordinates) {
		foreach($coordinates as $index => $coordinate) {
			if(rand(0,1)) {
				$amountOfAdditions = rand(0,5);
				$addition = 0;
				for ($i = 1; $i <= $amountOfAdditions; $i++) {
					$addition += rand(0, 100) / 100;
				}
				
				if ($coordinates[$index] === 100) {
					$coordinates[$index] = $coordinates[$index] - $addition;
				} elseif ($coordinates[$index] === 0) {
					$coordinates[$index] = $coordinates[$index] + $addition;
				} else {
					if(rand(0,1)) {
						$coordinates[$index] = $coordinates[$index] + $addition;
					} else {
						$coordinates[$index] = $coordinates[$index] - $addition;
					}
				}
				
				$coordinates[$index] = number_format($coordinates[$index], 2, '.', ' ');
			}
		}
		
		return $coordinates;
	}

/**
* Transform a set coordinates into coordinates formatted for css clip-path
* 
* @param array $coordinates A set of coordinates
* @return array
*/	
	private function transformToPercentages(array $coordinates) {
		foreach($coordinates as $index => $coordinate) {
			$coordinates[$index] = $coordinate . '%';
		}
		
		return $coordinates;
	}
	
/**
* Create start coordinates for a set of optional vertices according to a chosen amount of possible vertices per side
* 
* @param int $stepSize The percentage of distance between vertices per side
* @param int $stepsPerSide The amount of vertices per side
* @return array
*/	
	private function getOptionalVertexStartCoordinates(int $stepSize, int $stepsPerSide) {
		$optionalVertexStartCoordinates = [];
		for ($i = 0; $i < 4; $i++) {
			for ($j = 0; $j < $stepsPerSide; $j++) {
				if ($i === 0) {
					$newCoordinates = [0, $j * $stepSize]	;
				} elseif ($i === 1) {
					$newCoordinates = [$j * $stepSize, 100];
				} elseif ($i === 2) {
					$newCoordinates = [100, 100 - $j * $stepSize];
				} else {
					$newCoordinates = [100 - $j * $stepSize, 0];
				}
				
				$newKey = $stepsPerSide * $i + $j;
				if (empty($this->vertexStartCoordinates[$newKey])) {
					$optionalVertexStartCoordinates[$newKey] = $newCoordinates;
				}
			}
		}
		
		return $optionalVertexStartCoordinates;
	}

/**
* Generate a random number based on a binomial approximation of the Poisson distribution
* 
* @param int $mean The expected value of the distribution
* @param int $maximum The cut-off point of the distribution
* @return array
*/	
	private function getPoissonLikeRandomAmount(int $mean, int $maximum) {
		$randomAmount = 0;
		for ($i = 0; $i < $maximum ; $i++) {
			$x = rand(0, $maximum - 1);
			if ($x >= $mean) {
				$x = 0;
			} else {
				$x = 1;
			}
			
			$randomAmount += $x;
		} 
		
		return $randomAmount;
	}
}