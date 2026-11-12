<?php

namespace Tests\Unit;

use App\Services\WhatsApp\AttendanceMessageParser;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * The parser has no database behind it, so these run as plain unit tests.
 *
 * Most cases here are messy on purpose: a supervisor typing on a phone will
 * not produce clean input, and the point of the review screen is that nothing
 * is silently guessed at.
 */
class AttendanceMessageParserTest extends TestCase
{
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

    public function test_it_reads_a_clean_message(): void
    {
        $result = $this->parse("ATT 12-11-2026\nJZ-9036 P\nJZ-9019 A\nLE-9912 OFF");

        $this->assertTrue($result['is_attendance']);
        $this->assertSame('2026-11-12', $result['date']);
        $this->assertNull($result['date_error']);
        $this->assertCount(3, $result['lines']);

        $this->assertSame('JZ-9036', $result['lines'][0]['vehicle_token']);
        $this->assertSame(1, $result['lines'][0]['status']);
        $this->assertNull($result['lines'][0]['error']);

        $this->assertSame(2, $result['lines'][1]['status']);
        $this->assertSame(7, $result['lines'][2]['status']);
    }

    public function test_line_numbers_point_at_the_original_message(): void
    {
        $result = $this->parse("ATT\nJZ-9036 P\nJZ-9019 A");

        $this->assertSame(2, $result['lines'][0]['line_no']);
        $this->assertSame(3, $result['lines'][1]['line_no']);
    }

    public function test_a_message_without_the_keyword_is_not_attendance(): void
    {
        $result = $this->parse("Salam sir\nJZ-9036 P");

        $this->assertFalse($result['is_attendance']);
        $this->assertSame([], $result['lines']);
    }

    /**
     * Supervisors are Urdu speakers, so the Urdu word for attendance is
     * accepted alongside the English one.
     */
    public function test_it_accepts_the_urdu_keyword(): void
    {
        $this->assertTrue($this->parse("HAZRI\nJZ-9036 P")['is_attendance']);
        $this->assertTrue($this->parse("Attendance 12-11-2026\nJZ-9036 P")['is_attendance']);
    }

    public function test_a_missing_date_means_today(): void
    {
        $result = $this->parse("ATT\nJZ-9036 P");

        $this->assertSame('2026-11-12', $result['date']);
        $this->assertNull($result['date_error']);
    }

    public function test_it_accepts_the_date_formats_people_actually_use(): void
    {
        $this->assertSame('2026-11-10', $this->parse("ATT 10-11-2026\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-10', $this->parse("ATT 10/11/2026\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-10', $this->parse("ATT 2026-11-10\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-10', $this->parse("ATT 10.11.2026\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-01', $this->parse("ATT 1-11-2026\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-12', $this->parse("ATT today\nJZ-9036 P")['date']);
        $this->assertSame('2026-11-11', $this->parse("ATT yesterday\nJZ-9036 P")['date']);
    }

    /** Day before month: 10-11-2026 is 10 November, not 11 October. */
    public function test_the_day_comes_before_the_month(): void
    {
        $result = $this->parse("ATT 10-11-2026\nJZ-9036 P");

        $this->assertSame('2026-11-10', $result['date']);
    }

    public function test_a_station_name_after_the_date_is_ignored(): void
    {
        $result = $this->parse("ATT 10-11-2026 Johar\nJZ-9036 P");

        $this->assertSame('2026-11-10', $result['date']);
    }

    public function test_an_unreadable_date_is_reported_and_not_guessed(): void
    {
        $result = $this->parse("ATT 32-13-2026\nJZ-9036 P");

        $this->assertNull($result['date']);
        $this->assertNotNull($result['date_error']);

        // The lines are still read, so the reviewer only has to fix the date.
        $this->assertCount(1, $result['lines']);
    }

    public function test_a_future_date_is_flagged_but_kept(): void
    {
        $result = $this->parse("ATT 20-11-2026\nJZ-9036 P");

        $this->assertSame('2026-11-20', $result['date']);
        $this->assertStringContainsString('future', $result['date_error']);
    }

    public function test_case_spacing_and_dashes_do_not_matter(): void
    {
        $result = $this->parse("att\njz-9036 p\n  JZ 9019   a \nJZ9020,P\nJZ-9021: P");

        $this->assertCount(4, $result['lines']);

        foreach ($result['lines'] as $line) {
            $this->assertNull($line['error'], $line['raw'] . ' should have parsed');
        }

        $this->assertSame('JZ 9019', $result['lines'][1]['vehicle_token']);
        $this->assertSame('JZ9020', $result['lines'][2]['vehicle_token']);
    }

    public function test_a_vehicle_and_status_written_without_a_space(): void
    {
        $result = $this->parse("ATT\nJZ-9036P\nLE-9912OFF");

        $this->assertSame('JZ-9036', $result['lines'][0]['vehicle_token']);
        $this->assertSame(1, $result['lines'][0]['status']);
        $this->assertSame('LE-9912', $result['lines'][1]['vehicle_token']);
        $this->assertSame(7, $result['lines'][1]['status']);
    }

    public function test_a_numbered_list_is_accepted(): void
    {
        $result = $this->parse("ATT\n1. JZ-9036 P\n2) JZ-9019 A");

        $this->assertCount(2, $result['lines']);
        $this->assertSame('JZ-9036', $result['lines'][0]['vehicle_token']);
        $this->assertSame('JZ-9019', $result['lines'][1]['vehicle_token']);
    }

    public function test_full_words_and_numbers_work_as_status_codes(): void
    {
        $result = $this->parse("ATT\nJZ-9036 Present\nJZ-9019 absent\nJZ-9020 Leave\nJZ-9021 3");

        $this->assertSame(1, $result['lines'][0]['status']);
        $this->assertSame(2, $result['lines'][1]['status']);
        $this->assertSame(3, $result['lines'][2]['status']);
        $this->assertSame(3, $result['lines'][3]['status']);
    }

    /** Id 5 is spelled "Under maintanance" in the database; both spellings work. */
    public function test_both_spellings_of_maintenance_resolve_to_the_same_status(): void
    {
        $result = $this->parse("ATT\nJZ-9036 maintenance\nJZ-9019 maintanance\nJZ-9020 M");

        $this->assertSame(5, $result['lines'][0]['status']);
        $this->assertSame(5, $result['lines'][1]['status']);
        $this->assertSame(5, $result['lines'][2]['status']);
    }

    /**
     * An unknown code is not dropped and not guessed at. It reaches the review
     * screen with the vehicle intact, so only the status needs correcting.
     */
    public function test_an_unknown_status_is_kept_with_an_error(): void
    {
        $result = $this->parse("ATT\nJZ-9036 X");

        $this->assertCount(1, $result['lines']);
        $this->assertSame('JZ-9036', $result['lines'][0]['vehicle_token']);
        $this->assertNull($result['lines'][0]['status']);
        $this->assertStringContainsString('not a known code', $result['lines'][0]['error']);
    }

    public function test_a_vehicle_with_no_status_is_kept_with_an_error(): void
    {
        $result = $this->parse("ATT\nJZ-9036");

        $this->assertCount(1, $result['lines']);
        $this->assertSame('No status code on this line', $result['lines'][0]['error']);
    }

    /** Pleasantries at the end of a message are not errors. */
    public function test_chatter_is_set_aside_rather_than_flagged(): void
    {
        $result = $this->parse("ATT\nJZ-9036 P\nThanks\nDone sir");

        $this->assertCount(1, $result['lines']);
        $this->assertSame(['Thanks', 'Done sir'], $result['notes']);
    }

    public function test_a_duplicated_vehicle_is_left_for_the_reviewer_to_resolve(): void
    {
        $result = $this->parse("ATT\nJZ-9036 P\nJZ-9036 A");

        // Both lines survive: deciding which one wins is a human judgement,
        // not something the parser should silently make.
        $this->assertCount(2, $result['lines']);
        $this->assertSame(1, $result['lines'][0]['status']);
        $this->assertSame(2, $result['lines'][1]['status']);
    }

    public function test_an_empty_message_produces_nothing(): void
    {
        $result = $this->parse('');

        $this->assertFalse($result['is_attendance']);
        $this->assertSame([], $result['lines']);
    }

    public function test_a_header_with_no_lines_is_still_attendance(): void
    {
        $result = $this->parse('ATT 12-11-2026');

        $this->assertTrue($result['is_attendance']);
        $this->assertSame([], $result['lines']);
    }

    public function test_windows_line_endings_are_handled(): void
    {
        $result = $this->parse("ATT\r\nJZ-9036 P\r\nJZ-9019 A");

        $this->assertCount(2, $result['lines']);
    }

    public function test_vehicle_tokens_normalise_to_the_same_key(): void
    {
        $expected = 'JZ9036';

        $this->assertSame($expected, AttendanceMessageParser::normalizeVehicleToken('JZ-9036'));
        $this->assertSame($expected, AttendanceMessageParser::normalizeVehicleToken('jz 9036'));
        $this->assertSame($expected, AttendanceMessageParser::normalizeVehicleToken('  JZ9036 '));
        $this->assertSame($expected, AttendanceMessageParser::normalizeVehicleToken('JZ/9036'));
        $this->assertSame('', AttendanceMessageParser::normalizeVehicleToken(null));
    }
}
