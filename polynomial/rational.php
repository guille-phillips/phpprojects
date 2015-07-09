<?php

	class Rational {
		public $numerator = 0;
		public $denominator = 1;
		public $sign = 1;

		public function __construct($numerator, $denominator) {
			$this->numerator = abs($numerator);
			$this->denominator = abs($denominator);
			$this->sign = (($numerator<0)?-1:1)*(($denominator<0)?-1:1);
			$this->Normalise();
		}

		private function Normalise() {
			$gcd = $this->GCD();
			$this->numerator = $this->numerator / $gcd;
			$this->denominator = $this->denominator /$gcd;
		}

		public function Add($rational) {
			$result = new Rational($this->numerator*$this->sign*$rational->denominator+$this->denominator*$rational->numerator*$rational->sign,$this->denominator*$rational->denominator);

			return $result;
		}


		public function Sub($rational) {
			$result = new Rational($this->numerator*$this->sign*$rational->denominator-$this->denominator*$rational->numerator*$rational->sign,$this->denominator*$rational->denominator);
				
			return $result;
		}


		public function Mult($rational) {
			$result = new Rational($this->numerator*$this->sign*$rational->numerator*$rational->sign,$this->denominator*$rational->denominator);

			return $result;
		}


		public function Div($rational) {
			$result = new Rational($this->numerator*$this->sign*$rational->denominator*$rational->sign,$this->denominator*$rational->numerator);

			return $result;
		}

		public function Pow($rational) {

		}

		public function GCD() {
			$n1 = $this->numerator;
			$n2 = $this->denominator;

			do {
				while ($n2<$n1) {
					$n1 -= $n2;
				}
				$temp = $n1;
				$n1 = $n2;
				$n2 = $temp;

			} while ($n1>$n2);

			return $n1;
		}
	}

	class Polynomial {
		private $coefficients;

		public function __construct($coefficients) {
			$this->coefficients = $coefficients;
		}
	}

	$test = new Rational(3,-2);
	$t2 = $test->Div(new Rational(-2,3));

	//$test = new Rational(34,14);
	//echo $test->GCD();
	$a=1;
	