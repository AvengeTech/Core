<?php

namespace core\discord\task;

use pocketmine\scheduler\AsyncTask;

use core\discord\objects\Post;

class SendPostTask extends AsyncTask {

	public string $data;
	public string $url;

	public function __construct(Post $post) {
		$this->data = json_encode($post->toArray());
		$this->url = $post->getWebhook()->getUrl();
	}

	public function onRun(): void {
		$json_data = $this->data;
		$url = $this->url;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$this->setResult(curl_exec($ch));
		curl_close($ch);
	}

	public function onCompletion(): void {
	}
}
