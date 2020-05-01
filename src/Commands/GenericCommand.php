<?php

namespace Qortex\Bootstrap\Commands;

use Illuminate\Console\Command;

class GenericCommand extends Command
{
	private function emptyLine()
	{
		$this->line('');
	}

	private function errorBorder($length)
	{
		parent::error(str_repeat(' ', $length));
	}

	private function errorMessage($string)
	{
		parent::error('  ' . $string . '  ');
	}

	public function error($string, $verbosity = null)
	{
		$panelWidth = mb_strlen($string) + 4;

		$this->emptyLine();
		$this->errorBorder($panelWidth);
		$this->errorMessage($string, $verbosity);
		$this->errorBorder($panelWidth);
		$this->emptyLine();
	}
}
