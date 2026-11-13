<?php

namespace Tests\Unit;

use App\Services\WhatsApp\AttendanceMessageParser;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * The parser has no database behind it, so these run as plain unit tests.
 *
 * Every message here is transcribed from the real supervisor groups rather
 * than invented: the bullets, the equals signs, the emoji statuses, the names
 * between vehicle and status, the lines with no status at all. Nothing in the
 * parser may assume a format anybody was asked to adopt.
 */
class AttendanceMessageParserTest extends TestCase
{
    /** 🅿️ and 🅰️ as WhatsApp actually sends them. */
    private const P = "\u{1F17F}\u{FE0F}";
    private const A = "\u{1F170}\u{FE0F}";

    private AttendanceMessageParser $parser;

    private CarbonImmutable $receivedAt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new AttendanceMessageParser();
        $this->receivedAt = CarbonImmutable::parse('2026-11-12 08:30:00');
    }

    private function parse(string $body): array
    {
        return $this->parser->parse($body, $this->receivedAt);
    }

    // -----------------------------------------------------------------
    // Format A - Orangi & Garden: bullets, names, emoji statuses
    // -----------------------------------------------------------------

    public function test_it_reads_the_orangi_register(): void
    {
        $result = $this->parse(
            "Orengi & garden driver's attendence\n"
            . '1•JZ 9785 shahinsha ' . self::P . "\n"
            . '2•JZ 9787 Adnan khan ' . self::P . "\n"
            . '4•JZ 9677 Rahat ' . self::P
        );

        $this->assertTrue($result['is_attendance']);
        $this->assertSame(AttendanceMessageParser::KIND_DRIVER, $result['kind']);
        $this->assertSame("Orengi & garden driver's attendence", $result['header']);
        $this->assertCount(3, $result['lines']);

        $first = $result['lines'][0];
        $this->assertSame('JZ 9785', $first['vehicle_token']);
        $this->assertCount(1, $first['people']);
        $this->assertSame('shahinsha', $first['people'][0]['name']);
        $this->assertSame(1, $first['people'][0]['status']);
    }

    /** No date is sent, so the day the message arrived is used. */
    public function test_a_register_with_no_date_uses_the_day_it_arrived(): void
    {
        $result = $this->parse(
            "Orengi & garden driver's attendence\n"
            . '1•JZ 9785 shahinsha ' . self::P . "\n"
            . '2•JZ 9787 Adnan khan ' . self::P . "\n"
            . '3•JZ 9737 Umair jude ' . self::P
        );

        $this->assertSame('2026-11-12', $result['date']);
        $this->assertNull($result['date_error']);
    }

    /**
     * "13•LE 1963 Nizam 🅰️ Imran akhtr 🅿️" - the man on the vehicle is absent
     * and the next name is who actually drove. Both have to survive.
     */
    public function test_an_absence_and_its_replacement_are_both_read(): void
    {
        $result = $this->parse(
            "Orengi attendence\n"
            . '12•KP 9315 Imran ahemd ' . self::P . "\n"
            . '13•LE 1963 Nizam ' . self::A . ' Imran akhtr ' . self::P . "\n"
            . '14•LE 9927 A waseem & Habib ' . self::P
        );

        $line = $result['lines'][1];
        $this->assertSame('LE 1963', $line['vehicle_token']);
        $this->assertCount(2, $line['people']);

        $this->assertSame('Nizam', $line['people'][0]['name']);
        $this->assertSame(2, $line['people'][0]['status'], 'Nizam is absent');

        $this->assertSame('Imran akhtr', $line['people'][1]['name']);
        $this->assertSame(1, $line['people'][1]['status'], 'his replacement is present');
    }

    /**
     * "14•LE 9927 A waseem & Habib 🅿️" - the leading "A" is an initial, not
     * Absent. Reading it as a status would mark the wrong man away.
     */
    public function test_an_initial_is_not_mistaken_for_absent(): void
    {
        $result = $this->parse(
            "Orengi attendence\n"
            . '1•JZ 9785 shahinsha ' . self::P . "\n"
            . '2•JZ 9787 Adnan khan ' . self::P . "\n"
            . '14•LE 9927 A waseem & Habib ' . self::P
        );

        $line = $result['lines'][2];
        $this->assertCount(1, $line['people']);
        $this->assertSame('A waseem & Habib', $line['people'][0]['name']);
        $this->assertSame(1, $line['people'][0]['status']);
    }

    // -----------------------------------------------------------------
    // Format B - equals signs, names, no status marker at all
    // -----------------------------------------------------------------

    public function test_it_reads_a_register_with_no_status_markers(): void
    {
        $result = $this->parse("26=Jz-9640  Umair\n27=Jz-8959  Faizan\n37=Le-9907  Shabruk");

        $this->assertTrue($result['is_attendance']);
        $this->assertSame(AttendanceMessageParser::KIND_DRIVER, $result['kind']);
        $this->assertCount(3, $result['lines']);

        $first = $result['lines'][0];
        $this->assertSame('Jz-9640', $first['vehicle_token']);
        $this->assertSame('Umair', $first['people'][0]['name']);

        // No marker means no status. What to assume is the builder's call,
        // and the review screen says so on the row.
        $this->assertNull($first['people'][0]['status']);
    }

    /** "33=Jz-9744  Gachi (L)Shaid hussain" - on leave, and who covered. */
    public function test_an_inline_leave_marker_splits_the_two_names(): void
    {
        $result = $this->parse(
            "26=Jz-9640  Umair\n"
            . "33=Jz-9744  Gachi (L)Shaid hussain\n"
            . '54=Lf-1068 Shahwazir( L)Shakeel'
        );

        foreach ([1, 2] as $index) {
            $line = $result['lines'][$index];
            $this->assertCount(2, $line['people'], $line['raw']);
            $this->assertSame(3, $line['people'][0]['status'], 'first name is on leave');
            $this->assertNull($line['people'][1]['status'], 'the stand-in carries no marker');
        }

        $this->assertSame('Gachi', $result['lines'][1]['people'][0]['name']);
        $this->assertSame('Shaid hussain', $result['lines'][1]['people'][1]['name']);
        $this->assertSame('Shahwazir', $result['lines'][2]['people'][0]['name']);
        $this->assertSame('Shakeel', $result['lines'][2]['people'][1]['name']);
    }

    /** "34=Jz-9676.  Saud" - a stray full stop after the number. */
    public function test_a_stray_full_stop_does_not_break_the_vehicle_number(): void
    {
        $result = $this->parse("26=Jz-9640  Umair\n34=Jz-9676.  Saud\n38=Le-9908.  Asaad");

        $this->assertSame('Jz-9676', $result['lines'][1]['vehicle_token']);
        $this->assertSame('Saud', $result['lines'][1]['people'][0]['name']);
    }

    /** The same vehicle on two lines with two drivers is left as two lines. */
    public function test_one_vehicle_named_twice_stays_two_lines(): void
    {
        $result = $this->parse("30=Jz-9641  per rehman\n31=Jz-9641  Naveed\n32=Jz-9769  Javeed");

        $this->assertCount(3, $result['lines']);
        $this->assertSame('per rehman', $result['lines'][0]['people'][0]['name']);
        $this->assertSame('Naveed', $result['lines'][1]['people'][0]['name']);
    }

    // -----------------------------------------------------------------
    // Format C - the vehicle register
    // -----------------------------------------------------------------

    public function test_it_reads_the_vehicle_register_and_its_date(): void
    {
        $result = $this->parse(
            "12- NOV -2026 KORANGI\nVEHICLES ATTENDANCE\n\n1= Jz 9673 (P)\n3= Jz 9782 (P)\n22= Le. 5764 (P)"
        );

        $this->assertTrue($result['is_attendance']);
        $this->assertSame(AttendanceMessageParser::KIND_VEHICLE, $result['kind']);
        $this->assertSame('2026-11-12', $result['date']);
        $this->assertCount(3, $result['lines']);

        $this->assertSame('Jz 9673', $result['lines'][0]['vehicle_token']);
        $this->assertSame(1, $result['lines'][0]['people'][0]['status']);
        $this->assertSame('Le. 5764', $result['lines'][2]['vehicle_token']);
    }

    /** "2= Jz 9741 (P" - the closing bracket is often missed. */
    public function test_an_unclosed_bracket_still_reads_as_a_status(): void
    {
        $result = $this->parse("1= Jz 9673 (P)\n2= Jz 9741 (P\n3= Jz 9782 (P)");

        $this->assertSame(1, $result['lines'][1]['people'][0]['status']);
    }

    /** "14=Jz-9670 (Day)" and "(Night)" are shifts, not statuses. */
    public function test_a_shift_in_brackets_is_not_a_status(): void
    {
        $result = $this->parse("14=Jz-9670  (Day)\n15=Jz-9670  (Night)\n16=Jz-9669 ( Morning)");

        $this->assertSame('Day', $result['lines'][0]['shift']);
        $this->assertSame('Night', $result['lines'][1]['shift']);
        $this->assertSame('Morning', $result['lines'][2]['shift']);

        foreach ($result['lines'] as $line) {
            foreach ($line['people'] as $person) {
                $this->assertNull($person['status'], 'a shift must not set a status');
            }
        }
    }

    /** "8= Jz 9853 inspection replecment LE-1940 (P)" - a stand-in vehicle. */
    public function test_a_replacement_vehicle_is_picked_up_separately(): void
    {
        $result = $this->parse(
            "1= Jz 9673 (P)\n2= Jz 9741 (P)\n8= Jz 9853 inspection replecment LE-1940 (P)"
        );

        $line = $result['lines'][2];
        $this->assertSame('Jz 9853', $line['vehicle_token']);
        $this->assertSame('LE-1940', $line['second_vehicle_token']);
        $this->assertContains('inspection', $line['keywords']);
        $this->assertContains('replecment', $line['keywords']);
    }

    // -----------------------------------------------------------------
    // Telling attendance from ordinary chat
    // -----------------------------------------------------------------

    /**
     * No keyword is required, because the real registers carry none. What
     * marks a message out is that it is mostly a list of vehicle numbers.
     */
    public function test_ordinary_chat_is_not_read_as_attendance(): void
    {
        $this->assertFalse($this->parse('Sir gaari kharab ho gayi hai')['is_attendance']);
        $this->assertFalse($this->parse("Salam sir\nkal chutti hai?")['is_attendance']);
        $this->assertFalse($this->parse('')['is_attendance']);
    }

    /** One or two vehicles mentioned in passing is a conversation, not a register. */
    public function test_a_passing_mention_of_a_vehicle_is_not_a_register(): void
    {
        $result = $this->parse("Sir JZ-9036 workshop mein hai\nLE 1940 bhi kharab hai");

        $this->assertFalse($result['is_attendance']);
    }

    public function test_closing_pleasantries_are_set_aside(): void
    {
        $result = $this->parse(
            "26=Jz-9640  Umair\n27=Jz-8959  Faizan\n37=Le-9907  Shabruk\nThanks sir\nDone"
        );

        $this->assertCount(3, $result['lines']);
        $this->assertSame(['Thanks sir', 'Done'], $result['notes']);
    }

    public function test_windows_line_endings_are_handled(): void
    {
        $result = $this->parse("26=Jz-9640  Umair\r\n27=Jz-8959  Faizan\r\n37=Le-9907  Shabruk");

        $this->assertCount(3, $result['lines']);
    }

    // -----------------------------------------------------------------
    // Vehicle numbers
    // -----------------------------------------------------------------

    public function test_vehicle_tokens_normalise_to_the_same_key(): void
    {
        $expected = 'JZ9036';

        foreach (['JZ-9036', 'jz 9036', 'Jz.9036', '  JZ9036 ', 'JZ/9036'] as $written) {
            $this->assertSame($expected, AttendanceMessageParser::normalizeVehicleToken($written), $written);
        }

        $this->assertSame('', AttendanceMessageParser::normalizeVehicleToken(null));
    }

    /** Every prefix seen in the real groups has to be recognised. */
    public function test_every_prefix_the_groups_use_is_recognised(): void
    {
        $body = "1•JZ 9785 a\n2•LE 9917 b\n3•LF 1073 c\n4•KP 9315 d\n5=Kq-6020 e\n6=Ld-0718 f\n7=Jv-8188 g";

        $result = $this->parse($body);

        $this->assertCount(7, $result['lines']);
        $this->assertSame(
            ['JZ 9785', 'LE 9917', 'LF 1073', 'KP 9315', 'Kq-6020', 'Ld-0718', 'Jv-8188'],
            array_column($result['lines'], 'vehicle_token')
        );
    }

    public function test_line_numbers_point_back_at_the_original_message(): void
    {
        $result = $this->parse("Orengi attendence\n1•JZ 9785 a " . self::P . "\n2•JZ 9787 b " . self::P . "\n3•JZ 9737 c " . self::P);

        $this->assertSame([2, 3, 4], array_column($result['lines'], 'line_no'));
    }
}
