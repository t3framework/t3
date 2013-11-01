<?php


class Less_Tree_Unit{

	//public $type = 'Unit';
	var $numerator = array();
	var $denominator = array();

	function __construct($numerator = array(), $denominator = array(), $backupUnit = null ){
		$this->numerator = $numerator;
		$this->denominator = $denominator;
		$this->backupUnit = $backupUnit;
	}

	function __clone(){
		$this->numerator = array_slice($this->numerator,0);
		$this->denominator = array_slice($this->denominator,0);
	}

	function toCSS($env){

		if( count($this->numerator) ){
			return $this->numerator[0];
		}
		if( count($this->denominator) ){
			return $this->denominator[0];
		}
		if( (!$env || !$env->strictUnits) && $this->backupUnit ){
			return $this->backupUnit;
		}
		return "";
	}

	function toString(){
		$returnStr = implode('*',$this->numerator);
		foreach($this->denominator as $d){
			$returnStr .= '/'.$d;
		}
		return $returnStr;
	}

	function compare($other) {
		return $this->is( $other->toString() ) ? 0 : -1;
	}

	function is($unitString){
		return $this->toString() === $unitString;
	}

	function isAngle() {
		return isset( Less_Tree_UnitConversions::$angle[$this->toCSS()] );
	}

	function isEmpty(){
		return count($this->numerator) === 0 && count($this->denominator) === 0;
	}

	function isSingular() {
		return count($this->numerator) <= 1 && count($this->denominator) == 0;
	}


	function usedUnits(){
		$result = array();

		foreach(Less_Tree_UnitConversions::$groups as $groupName){
			$group = Less_Tree_UnitConversions::${$groupName};

			for($i=0; $i < count($this->numerator); $i++ ){
				$atomicUnit = $this->numerator[$i];
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}

			for($i=0; $i < count($this->denominator); $i++ ){
				$atomicUnit = $this->denominator[$i];
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}
		}

		return $result;
	}

	function cancel(){
		$counter = array();
		$backup = null;

		for( $i = 0; $i < count($this->numerator); $i++ ){
			$atomicUnit = $this->numerator[$i];
			if( !$backup ){
				$backup = $atomicUnit;
			}
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) + 1;
		}

		for( $i = 0; $i < count($this->denominator); $i++ ){
			$atomicUnit = $this->denominator[$i];
			if( !$backup ){
				$backup = $atomicUnit;
			}
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) - 1;
		}

		$this->numerator = array();
		$this->denominator = array();

		foreach($counter as $atomicUnit => $count){
			if( $count > 0 ){
				for( $i = 0; $i < $count; $i++ ){
					$this->numerator[] = $atomicUnit;
				}
			}elseif( $count < 0 ){
				for( $i = 0; $i < -$count; $i++ ){
					$this->denominator[] = $atomicUnit;
				}
			}
		}

		if( count($this->numerator) === 0 && count($this->denominator) === 0 && $backup ){
			$this->backupUnit = $backup;
		}

		sort($this->numerator);
		sort($this->denominator);
	}


}

