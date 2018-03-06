<?php
namespace SpyHelper;

use JsonSerializable;
use SimpleXMLElement;

/**
 * Class JsonSimpleXMLElementDecorator
 *
 * @package SpyHelper
 */
class JsonSimpleXMLElementDecorator implements JsonSerializable
{
	const Depth	= 1024;

	/** @var SimpleXMLElement **/
	private $oXml;	
	/** @var bool **/
	private $bConvertAttributesToProperties = false;
	/** @var int **/
	private $iDepth;

	/**
	 * JsonSimpleXMLElementDecorator constructor.
	 *
	 * @param SimpleXMLElement $oElement
	 * @param bool             $bConvertAttributesToProperties
	 * @param int              $iDepth
	 */
	public function __construct(SimpleXMLElement $oElement, bool $bConvertAttributesToProperties, int $iDepth = self::Depth)
	{
		$this->oXml								= $oElement;
		$this->bConvertAttributesToProperties	= $bConvertAttributesToProperties;
		$this->iDepth							= $iDepth;
	}
	
	/**
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		$oCurrent	= $this->oXml;

		$mReturn	= [];

		$arrAttributes = $oCurrent->attributes();

		if($arrAttributes !== null)
		{
			$arrAttributes	= array_map('strval', iterator_to_array($arrAttributes));
			if($this->bConvertAttributesToProperties)
			{
				$mReturn	= $arrAttributes;
			}
			else
			{
				$mReturn['@attributes']	= $arrAttributes;
			}
		}

		$oChildren	= $oCurrent;
		$iDepth		= $this->iDepth - 1;
		if($iDepth <= 0)
		{
			$oChildren	= [];
		}

		foreach($oChildren as $strName => $oElement)
		{
			$oDecorator	= new self($oElement, $this->bConvertAttributesToProperties, $iDepth);

			if(isset($mReturn[$strName]))
			{
				if(!is_array($mReturn[$strName]))
				{
					$mReturn[$strName]	= [$mReturn[$strName]];
				}
				$mReturn[$strName][]	= $oDecorator;
			}
			else
			{
				$mReturn[$strName]	= $oDecorator;
			}
		}

		$strText	= trim($oCurrent);

		if(strlen($strText) > 0)
		{
			if(count($mReturn) > 0)
			{
				$mReturn['@text']	= $strText;
			}
			else
			{
				$mReturn	= $strText;
			}
		}

		if($mReturn === '' || count($mReturn) === 0)
		{
			$mReturn	= null;
		}

		return $mReturn;
	}
}
