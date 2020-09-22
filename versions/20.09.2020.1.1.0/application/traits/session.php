<?php
	trait sessionManager 
	{
		
		public function startSession() 
		{
			session_start();
		}
		public function getSessionVar($reference) 
		{
			echo $_SESSION[$reference];
		}
		public function setSessionVar($reference, $value) 
		{
			$_SESSION[$reference]=$value;
		}
		
	}