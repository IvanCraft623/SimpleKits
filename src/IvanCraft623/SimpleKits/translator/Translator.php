<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\translator;

use IvanCraft623\SimpleKits\SimpleKits;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class Translator {
	use SingletonTrait;

	public const MINECRAFT_LOCALES = [ # https://minecraft.fandom.com/wiki/Language
		"bg_BG", # Bulgarian
		"cs_CZ", # Czech
		"da_DK", # Danish
		"de_DE", # German
		"el_GR", # Greek
		"en_GB", # British English
		"en_US", # American English
		"es_ES", # Spanish
		"es_MX", # Mexican Spanish
		"fi_FI", # Finnish
		"fr_CA", # Canadian French
		"fr_FR", # French
		"hu_HU", # Hungarian
		"id_ID", # Indonesian
		"it_IT", # Italian
		"ja_JP", # Japanese
		"ko_KR", # Korean
		"nl_NL", # Dutch
		"nb_NO", # Norwegian Bokmål
		"pl_PL", # Polish
		"pt_BR", # Brazilian Portuguese
		"pt_PT", # Portuguese
		"ru_RU", # Russian
		"sk_SK", # Slovak
		"sv_SE", # Swedish
		"tr_TR", # Turkish
		"uk_UA", # Ukrainian
		"zh_CN", # Chinese Simplified
		"zh_TW"  # Chinese Traditional
	];

	private SimpleKits $plugin;

	/** @var Language[] */
	private array $languages = [];

	private ?Language $defaultLanguage = null;
	
	public function __construct() {
		$this->plugin = SimpleKits::getInstance();
		$this->plugin->getDataManager()->saveLanguageResources();
	}

	public function getDefaultLanguage(): ?Language {
		return $this->defaultLanguage;
	}

	public function setDefaultLanguage(Language $language): void {
		$this->defaultLanguage = $language;
	}

	public function registerLanguage(Language $language): void {
		if (!in_array($language->getLocale(), self::MINECRAFT_LOCALES, true)) {
			$this->plugin->getLogger()->error("Language ".$language->getLocale()." is not available in minecraft!");
			return;
		}
		$this->languages[$language->getLocale()] = $language;
	}

	public function getLanguages(): array {
		return $this->languages;
	}

	public function getLanguage(string $locale): ?Language {
		return $this->languages[$locale] ?? null;
	}

	public function translate(CommandSender $target, string $key, array $replacements = []): string {
		$keys = array_merge(["%prefix"], array_keys($replacements));
		$values = array_merge([$this->plugin->getPrefix()], array_values($replacements));

		$language = (($target instanceof Player) ? ($this->languages[$target->getLocale()] ?? null) : null) ?? $this->defaultLanguage;
		$translation = $language->getTranslation($key);

		if ($translation === null) {
			$defaultTranslation = $this->defaultLanguage->getTranslation($key);
			if ($defaultTranslation === null) {
				$this->plugin->getLogger()->error("Unknown translation key ".$key);
				return "";
			} else {
				$translation = $defaultTranslation;
			}
		}
		return str_replace($keys, $values, $translation);
	}
}
