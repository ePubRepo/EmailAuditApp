<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class OAuthUtil
{
	/*
	 * @param $input
	 * @return URL-encoded value of $input
	 */
	public static function urlencode_rfc3986($input)
	{
		if (is_array($input))
		{
			return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
		}
		else if (is_scalar($input))
		{
			return str_replace(
				'+',
				' ',
				str_replace('%7E', '~', rawurlencode($input))
			);
		}
		else
		{
			return '';
		}
	}
}

?>
