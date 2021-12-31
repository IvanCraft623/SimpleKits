<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\translator;

class Language {

	private string $locale;

	private string $name;

	private array $translations;
	
	public function __construct(string $locale, array $translations) {
		$this->locale = $locale;
		$this->name = $translations["name"];
		$this->translations = $translations;
	}

	public function getLocale(): string {
		return $this->locale;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getTranslations(): array {
		return $this->translations;
	}

	public function getTranslation(string $key): ?string {
		return $this->translations[$key] ?? null;
	}
}
