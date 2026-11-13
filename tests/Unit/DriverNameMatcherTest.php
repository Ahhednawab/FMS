<?php

namespace Tests\Unit;

use App\Services\WhatsApp\DriverNameMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every pair here is real: the left side is what a supervisor typed in the
 * WhatsApp group, the right side is the name as the driver register holds it.
 *
 * The matcher decides who gets marked present, so being wrong here is worse
 * than being unsure. The must-not-match cases matter as much as the rest.
 */
class DriverNameMatcherTest extends TestCase
{
    private DriverNameMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new DriverNameMatcher();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function realPairs(): array
    {
        return [
            'run together'        => ['shahinsha', 'Shahin shah'],
            'surname only'        => ['durrani', 'Asif Khan Durrani'],
            'first part only'     => ['Nizam', 'Nizam-uddin'],
            'initial expanded'    => ['irfan m', 'Irfan Muhammad'],
            'short form'          => ['mala', 'Malang Shah'],
            'split differently'   => ['nandlal', 'Nand Lal'],
            'extra surname'       => ['Imran ahemd', 'Imran Ahmed Khan'],
            'sounds alike'        => ['Shabruk', 'Shahrukh'],
            'doubled vowel'       => ['Asaad', 'Asad Ali'],
            'title dropped'       => ['Masroor', 'Syed Masroor Ali'],
            'given name'          => ['Aftab', 'Aftab Ali'],
            'spaces lost'         => ['Shahwazir', 'Shah Wazir Khan'],
            'middle name dropped' => ['Rana adnan', 'Rana Muhammad Adnan'],
            'phonetic spelling'   => ['Juneed', 'Junaid Ali'],
            'initial after name'  => ['Akram R', 'Muhammad Akram'],
            'vowel swapped'       => ['Sheraz', 'Muhammad Shiraz'],
            'common prefix'       => ['zeshan', 'Muhammad Zeeshan'],
            'consonant swapped'   => ['shezad', 'Shahzad'],
            'run on ending'       => ['mattiullha', 'Matiullah'],
            'exact'               => ['Naveed', 'Muhammad Naveed'],
            'partial surname'     => ['Talib', 'Talib Hussain'],
            'single word'         => ['Phullan', 'Phullan'],
        ];
    }

    #[DataProvider('realPairs')]
    public function test_it_recognises_the_names_supervisors_actually_write(string $written, string $recorded): void
    {
        $score = $this->matcher->score($written, $recorded);

        $this->assertGreaterThanOrEqual(
            DriverNameMatcher::CONFIDENT,
            $score,
            sprintf('"%s" should be recognised as "%s" (scored %.2f)', $written, $recorded, $score)
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function differentPeople(): array
    {
        return [
            'nothing alike'       => ['Kareem', 'Kamran'],
            'different men'       => ['Faizan', 'Muhammad Sajjad'],
            'shared common word'  => ['Muhammad Nadeem', 'Muhammad Akram'],
            'unrelated'           => ['Gachi', 'Shakir'],
            'same first letter'   => ['Saud', 'Zahid'],
            'common word only'    => ['Umair', 'Muhammad Yousuf'],
            'not a near miss'     => ['jawad', 'Umair Jude'],
        ];
    }

    #[DataProvider('differentPeople')]
    public function test_it_does_not_confuse_two_different_drivers(string $written, string $recorded): void
    {
        $score = $this->matcher->score($written, $recorded);

        $this->assertLessThan(
            DriverNameMatcher::CONFIDENT,
            $score,
            sprintf('"%s" must not be taken for "%s" (scored %.2f)', $written, $recorded, $score)
        );
    }

    /** Sharing "Muhammad" must not pull two unrelated names together. */
    public function test_a_common_word_on_its_own_is_not_a_match(): void
    {
        $this->assertLessThan(
            DriverNameMatcher::FLOOR,
            $this->matcher->score('Muhammad Akram', 'Muhammad Nadeem Khan')
        );
    }

    public function test_it_ranks_the_right_driver_first(): void
    {
        $drivers = [
            (object) ['id' => 1, 'full_name' => 'Muhammad Yousuf'],
            (object) ['id' => 2, 'full_name' => 'Muhammad Zeeshan'],
            (object) ['id' => 3, 'full_name' => 'Zubair Ahmed'],
            (object) ['id' => 4, 'full_name' => 'Shahzad'],
        ];

        $ranked = $this->matcher->rank('zeshan', $drivers);

        $this->assertNotEmpty($ranked);
        $this->assertSame(2, $ranked[0]['id']);
        $this->assertGreaterThanOrEqual(DriverNameMatcher::CONFIDENT, $ranked[0]['score']);
    }

    public function test_it_returns_nothing_for_a_name_that_matches_nobody(): void
    {
        $drivers = [
            (object) ['id' => 1, 'full_name' => 'Muhammad Yousuf'],
            (object) ['id' => 2, 'full_name' => 'Zubair Ahmed'],
        ];

        $this->assertSame([], $this->matcher->rank('Qwertyuiop', $drivers));
        $this->assertSame([], $this->matcher->rank('', $drivers));
    }

    public function test_an_exact_name_scores_full_marks(): void
    {
        $this->assertSame(1.0, $this->matcher->score('Nizam-uddin', 'Nizam-uddin'));
        $this->assertSame(1.0, $this->matcher->score('nand lal', 'Nand Lal'));
    }
}
