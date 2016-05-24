<?php
	if(file_exists(__DIR__ . "/../../../../../clients.json"))
		return json_decode(file_get_contents(__DIR__ . "/../../../../../clients.json"));
	else return json_decode(file_get_contents(__DIR__ . "/clients.json"));
	