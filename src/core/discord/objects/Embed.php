<?php

namespace core\discord\objects;

class Embed {

	public $title = "";
	public $type = "rich";

	public $description = "";

	public $titleUrl = "";

	public $timestamp;

	public $color;

	public $footer;
	public $image;
	public $thumbnail;

	public $author;

	public $fields = [];

	public function __construct(
		string $title,
		string $type = "rich",
		string $description = "",
		string $titleUrl = "",
		string $color = "ffffff",
		?Footer $footer = null,
		string $image = "",
		string $thumbnail = "",
		?Author $author = null,
		array $fields = []
	) {
		$this->title = $title;
		$this->type = $type;

		$this->description = $description;
		$this->titleUrl = $titleUrl;

		$this->color = hexdec($color);
		$this->footer = $footer;

		$this->image = $image;
		$this->thumbnail = $thumbnail;

		$this->author = $author;

		$this->fields = $fields;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getTitleUrl(): string {
		return $this->titleUrl;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function getFooter(): ?Footer {
		return $this->footer;
	}

	public function getImage(): string {
		return $this->image;
	}

	public function getThumbnail(): string {
		return $this->thumbnail;
	}

	public function getAuthor(): ?Author {
		return $this->author;
	}

	public function getFields(): array {
		return $this->fields;
	}

	public function toArray(): array {
		$footer = ($this->getFooter() == null ? ["text" => "", "icon_url" => ""] : $this->getFooter()->toArray());
		$author = ($this->getAuthor() == null ? [
				"name" => "AvengeTech",
				"url" => ""
			] : $this->getAuthor()->toArray()
		);

		$fields = [];
		foreach ($this->getFields() as $field) $fields[] = $field->toArray();

		return [
			"title" => $this->getTitle(),
			"type" => $this->getType(),
			"description" => $this->getDescription(),
			"url" => $this->getTitleUrl(),
			"color" => $this->getColor(),

			"footer" => $footer,
			"image" => [
				"url" => $this->getImage()
			],
			"thumbnail" => [
				"url" => $this->getThumbnail()
			],

			"author" => $author,

			"fields" => $fields
		];
	}
}
