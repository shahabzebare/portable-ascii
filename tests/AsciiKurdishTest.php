<?php

declare(strict_types=1);

namespace voku\tests;

use PHPUnit\Framework\TestCase;
use voku\helper\ASCII;

/**
 * Central Kurdish (Sorani, "ckb") in Arabic script, also used for Badini.
 *
 * @internal
 */
final class AsciiKurdishTest extends TestCase
{
    public function testCentralKurdishLanguageCode(): void
    {
        self::assertSame('ckb', ASCII::CENTRAL_KURDISH_LANGUAGE_CODE);
        self::assertSame('ckb', ASCII::getAllLanguages()['central_kurdish']);
    }

    public function testCentralKurdishToAscii(): void
    {
        $cases = [
            // cities
            ['هەولێر', 'hewler'],
            ['ھەولێر', 'hewler'], // heh doachashmee, as used on Kurdish Wikipedia
            ['دهۆک', 'dhok'],
            ['سلێمانی', 'slemani'],
            ['کەرکووک', 'kerkuk'],
            ['زاخۆ', 'zaxo'],
            ['هەڵەبجە', 'helebce'],
            ['چەمچەماڵ', 'chemchemal'],
            ['قەرەداغ', 'qeredagh'],
            // words and names
            ['ڕۆژ', 'roj'],
            ['ڤیان', 'vyan'],
            ['گوڵ', 'gul'],
            ['کوردستان', 'kurdstan'],
            ['نەورۆز', 'newroz'],
            ['پێشمەرگە', 'peshmerge'],
            ['شێخ', 'shex'],
            ['ژیان', 'jyan'],
            ['خوێندن', 'xwendn'],
            ['ئاڤ', 'av'],
            ['عەلی', 'eli'],
            // و and ی as consonants or vowels
            ['ئاو', 'aw'],
            ['شەو', 'shew'],
            ['یەک', 'yek'],
            ['دوو', 'du'],
            ['ڕووناک', 'runak'],
            ['کاروان', 'karwan'],
            ['ئەوەی', 'ewey'],
            ['نوێی', 'nwey'],
            ['دوای', 'dway'],
            ['پیاو', 'pyaw'],
            ['مێژووی', 'mejuy'],
            ['بووە', 'buwe'],
            ['کوردییەکان', 'kurdiyekan'],
            // Arabic keyboard forms of ک and ی
            ['كوردي', 'kurdi'],
            ['كۆيە', 'koye'],
            ['ڤيان', 'vyan'],
            // sentence and digits
            ['ڕۆژی نەورۆز لە هەولێر', 'roji newroz le hewler'],
            ['١٢٣', '123'],
        ];

        foreach ($cases as [$input, $expected]) {
            self::assertSame($expected, ASCII::to_ascii($input, ASCII::CENTRAL_KURDISH_LANGUAGE_CODE), $input);
        }
    }

    /**
     * Laravel's Str::slug() and Str::ascii() call to_ascii() with "en" by default.
     */
    public function testKurdishLettersAreKeptForTheDefaultLanguage(): void
    {
        $cases = [
            ['هەولێر', 'hewler'],
            ['دهۆک', 'dhok'],
            ['سلێمانی', 'slemany'],
            ['ڕۆژ', 'rozh'],
            ['ڤیان', 'vyan'],
            ['گوڵ', 'gol'],
        ];

        foreach ($cases as [$input, $expected]) {
            self::assertSame($expected, ASCII::to_ascii($input), $input);
        }
    }

    public function testCentralKurdishSlugify(): void
    {
        self::assertSame('roji-newroz-le-hewler', ASCII::to_slugify('ڕۆژی نەورۆز لە هەولێر', '-', 'ckb'));
    }

    public function testCentralKurdishExtraSymbols(): void
    {
        self::assertSame('2  ko  2  yeksan  4', ASCII::to_ascii('2 + 2 = 4', 'ckb', true, true));
    }

    /**
     * The Kurdish map must not change Persian, Arabic, Urdu or Pashto output.
     */
    public function testOtherArabicScriptLanguagesKeepTheirOutput(): void
    {
        // [language, input, output for the language, output for "en"]
        $cases = [
            ['fa', 'او', 'ao', 'ao'],
            ['fa', 'طاووس', 'taoos', 'taoos'],
            ['fa', 'خواهر', 'khoahr', 'khoahr'],
            ['fa', 'پاییز', 'payyz', 'payyz'],
            ['fa', 'عنوان', 'anoan', 'aanoan'],
            ['fa', 'دانشگاه تهران', 'danshgah thran', 'danshgah thran'],
            ['ar', 'والتي', 'oalty', 'oalty'],
            ['ar', 'كانوا', 'kanoa', 'kanoa'],
            ['ar', 'طاووس', 'taoos', 'taoos'],
            ['ar', 'السلام عليكم', 'alslam aalykm', 'alslam aalykm'],
            ['ur', 'اور', 'aor', 'aor'],
            ['ur', 'ہوائی', 'hoayy', 'oayy'],
            ['ur', 'اردو زبان', 'ardo zban', 'ardo zban'],
            ['ps', 'ښوونکی', 'Soonky', 'oonky'],
            ['ps', 'پښتو', 'pSto', 'pto'],
            ['ps', 'او', 'ao', 'ao'],
        ];

        foreach ($cases as [$language, $input, $expected, $expectedEn]) {
            self::assertSame($expected, ASCII::to_ascii($input, $language), "$input ($language)");
            self::assertSame($expected, ASCII::to_ascii($input, $language, true, false, false, true), "$input ($language, single chars only)");
            self::assertSame($expected, ASCII::to_ascii($input, $language, true, true), "$input ($language, extra symbols)");
            self::assertSame($expectedEn, ASCII::to_ascii($input), "$input (en)");

            // inputs longer than 64 bytes take a different replacement path
            $long = \str_repeat($input . ' ', 12);
            self::assertSame(\str_repeat($expected . ' ', 12), ASCII::to_ascii($long, $language), "$input ($language, long)");
            self::assertSame(\str_repeat($expectedEn . ' ', 12), ASCII::to_ascii($long), "$input (en, long)");
        }
    }

    /**
     * Multi-character keys made only of letters that Persian and Arabic also use
     * must give the same result as replacing letter by letter, except for "ckb".
     */
    public function testSharedLetterSequencesStayLetterByLetterForOtherLanguages(): void
    {
        $sequences = ['وا', 'او', 'وو', 'یی', 'یا', 'ای', 'وای', 'واو', 'یای', 'یاو', 'ووا', 'ووی', 'ووو', 'اوا', 'ییا'];
        foreach (ASCII::charsArrayWithSingleLanguageValues(false, false) as $key => $value) {
            $key = (string) $key;
            if (
                \preg_match('/^[\x{0600}-\x{06FF}]{2,}$/u', $key) === 1
                &&
                \preg_match('/[ەێۆڵڕڤھۊ]/u', $key) !== 1
            ) {
                $sequences[] = $key;
            }
        }

        foreach (\array_unique($sequences) as $sequence) {
            $letters = \preg_split('//u', $sequence, -1, \PREG_SPLIT_NO_EMPTY);
            foreach (['en', 'fa', 'ar', 'ur', 'ps'] as $language) {
                $letterByLetter = '';
                foreach ($letters as $letter) {
                    $letterByLetter .= ASCII::to_ascii($letter, $language);
                }

                self::assertSame($letterByLetter, ASCII::to_ascii($sequence, $language), "$sequence ($language)");
            }
        }
    }

    /**
     * ڤ (Arabic loanwords) and ھ (Urdu) were dropped for "en" before; they are mapped now.
     */
    public function testLettersThatWereDroppedBeforeAreNowTransliterated(): void
    {
        self::assertSame('vydyo', ASCII::to_ascii('ڤيديو', 'ar'));
        self::assertSame('vydyo', ASCII::to_ascii('ڤيديو'));
        self::assertSame('bhy', ASCII::to_ascii('بھی'));
        self::assertSame('bhy', ASCII::to_ascii('بھی', 'ur'));
    }
}
