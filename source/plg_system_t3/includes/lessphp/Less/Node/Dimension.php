<?php


class Less_Tree_Dimension{

	//public $type = 'Dimension';

    public function __construct($value, $unit = false){
        $this->value = floatval($value);

		if( $unit && ($unit instanceof Less_Tree_Unit) ){
			$this->unit = $unit;
		}elseif( $unit ){
			$this->unit = new Less_Tree_Unit( array($unit) );
		}else{
			$this->unit = new Less_Tree_Unit( );
		}
    }

	/*
	function accept( $visitor ){
		$visitor->visit( $this->unit );
	}
	*/

    public function compile($env = null) {
        return $this;
    }

    public function toColor() {
        return new Less_Tree_Color(array($this->value, $this->value, $this->value));
    }

	public function toCSS( $env = null ){

		if( ($env && $env->strictUnits) && !$this->unit->isSingular() ){
			throw new Less_CompilerException("Multiple units in dimension. Correct the units or use the unit function. Bad unit: ".$this->unit->toString());
		}

		$value = $this->value;
		$strValue = (string)$value;

		if( $value !== 0 && $value < 0.000001 && $value > -0.000001 ){
			// would be output 1e-6 etc.
			$strValue = number_format($strValue,10);
			$strValue = preg_replace('/\.?0+$/','', $strValue);
		}

		if( $env && $env->compress ){
			// Zero values doesn't need a unit
			if( $value === 0 && !$this->unit->isAngle() ){
				return $strValue;
			}

			// Float values doesn't need a leading zero
			if( $value > 0 && $value < 1 && $strValue[0] === '0' ){
				$strValue = substr($strValue,1);
			}
		}

		return $strValue . $this->unit->toCSS($env);
	}

    public function __toString(){
        return $this->toCSS();
    }

    // In an operation between two Dimensions,
    // we default to the first Dimension's unit,
    // so `1px + 2em` will yield `3px`.
    public function operate($env, $op, $other){

		$value = Less_Environment::operate($env, $op, $this->value, $other->value);
		$unit = clone $this->unit;

		if( $op === '+' || $op === '-' ){

			if( !count($unit->numerator) && !count($unit->denominator) ){
				$unit->numerator = array_slice($other->unit->numerator,0);
				$unit->denominator = array_slice($other->unit->denominator,0);
			}elseif( !count($other->unit->numerator) && !count($other->unit->denominator) ){
				// do nothing
			}else{
				$other = $other->convertTo( $this->unit->usedUnits());

				if( $env->strictUnits && $other->unit->toString() !== $unit->toCSS() ){
					throw new Less_CompilerException("Incompatible units. Change the units or use the unit function. Bad units: '".$unit->toString() . "' and ".$other->unit->toString()+"'.");
				}

				$value = Less_Environment::operate($env, $op, $this->value, $other->value);
			}
		}elseif( $op === '*' ){
			$unit->numerator = array_merge($unit->numerator, $other->unit->numerator);
			$unit->denominator = array_merge($unit->denominator, $other->unit->denominator);
			sort($unit->numerator);
			sort($unit->denominator);
			$unit->cancel();
		}elseif( $op === '/' ){
			$unit->numerator = array_merge($unit->numerator, $other->unit->denominator);
			$unit->denominator = array_merge($unit->denominator, $other->unit->numerator);
			sort($unit->numerator);
			sort($unit->denominator);
			$unit->cancel();
		}
		return new Less_Tree_Dimension( $value, $unit);
    }

	public function compare($other) {
		if ($other instanceof Less_Tree_Dimension) {

			$a = $this->unify();
			$b = $other->unify();
			$aValue = $a->value;
			$bValue = $b->value;

			if ($bValue > $aValue) {
				return -1;
			} elseif ($bValue < $aValue) {
				return 1;
			} else {
				if( !$b->unit->isEmpty() && $a->unit->compare($b->unit) !== 0) {
					return -1;
				}
				return 0;
			}
		} else {
			return -1;
		}
	}

	function unify() {
		return $this->convertTo(array('length'=> 'm', 'duration'=> 's', 'angle' => 'rad' ));
	}

    function convertTo($conversions) {
		$value = $this->value;
		$unit = clone $this->unit;

		if( is_string($conversions) ){
			$derivedConversions = array();
			foreach( Less_Tree_UnitConversions::$groups as $i ){
				if( isset(Less_Tree_UnitConversions::${$i}[$conversions]) ){
					$derivedConversions = array( $i => $conversions);
				}
			}
			$conversions = $derivedConversions;
		}


		foreach($conversions as $groupName => $targetUnit){
			$group = Less_Tree_UnitConversions::${$groupName};

			//numerator
			for($i=0; $i < count($unit->numerator); $i++ ){
				$atomicUnit = $unit->numerator[$i];
				if( !isset($group[$atomicUnit]) ){
					continue;
				}

				$value = $value * ($group[$atomicUnit] / $group[$targetUnit]);

				$unit->numerator[$i] = $targetUnit;
			}

			//denominator
			for($i=0; $i < count($unit->denominator); $i++ ){
				$atomicUnit = $unit->denominator[$i];
				if( !isset($group[$atomicUnit]) ){
					continue;
				}

				$value = $value / ($group[$atomicUnit] / $group[$targetUnit]);

				$unit->denominator[$i] = $targetUnit;
			}
		}

		$unit->cancel();

		return new Less_Tree_Dimension( $value, $unit);
    }
}
