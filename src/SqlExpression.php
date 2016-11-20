<?php

namespace jugger\db;

class SqlExpression
{
	public $builder;
	public $expression;

	public function __construct($expression, \Closure $builder = null) {
		$this->builder = $builder;
		$this->expression = $expression;
	}

	public function build() {
		return $this->__toString();
	}

	public function __toString() {
		if (is_null($this->builder)) {
			return $this->expression;
		}
		return $this->builder->call($this);
	}
}
