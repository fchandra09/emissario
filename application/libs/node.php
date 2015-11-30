<?php

class Node
{
	protected $_key;
	protected $_data;
	protected $_parent = null;
	protected $_children = array();

	public function __construct($key, $data)
	{
		$this->_key = $key;
		$this->_data = $data;
	}

	public function setKey($key)
	{
		$this->_key = $key;
	}

	public function getKey()
	{
		return $this->_key;
	}

	public function setData($data)
	{
		$this->_data = $data;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setParent(&$parent)
	{
		$this->_parent = $parent;
	}

	public function &getParent()
	{
		return $this->_parent;
	}

	public function addChild(&$child)
	{
		$this->_children[$child->getKey()] = $child;
	}

	public function &getChild($key)
	{
		return $this->_children[$key];
	}

}