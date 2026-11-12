<?php

namespace App\Services\WhatsApp;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeImmutable;

/**
 * Reads a supervisor WhatsApp message and works out what it says.
 *
 * Deliberately knows nothing about the database: it turns text into structured
 * lines and stops there, so every rule in it can be tested without a schema.
 * Resolving a vehicle number to a driver is AttendanceDraftBuilder.
 *
 * The expected shape is:
 *
 *     ATT 12-11-2026
 *     JZ-9036 P
 *     JZ-9019 A
 *     LE-9912 OFF
 *
 * Nothing that cannot be understood is thrown away. A line that fails to parse
 * comes back with an `error` so it still reaches the review screen, where a
 * person can see what the supervisor actually typed.
 */
class AttendanceMessageParser
{
    /**
     * The words that mark a message as attendance. Anything else is treated as
     * ordinary chat and never becomes a draft.
     */
    public const HEADER_KEYWORDS = ['ATT', 'ATTENDANCE', 'HAZRI', 'HAZIRI', 'HAJRI'];

    /**
     * Status code to `attendance_status.id`. Both the short code a supervisor
     * will actually type and the full word are accepted.
     *
     * (Id 5 is spelled "Under maintanance" in the database; both spellings
     * are accepted here so a correctly spelled message still works.)
     */
    public const STATUS_CODES = [
        'P' => 1,
        'PRESENT' => 1,
        'A' => 2,
        'ABSENT' => 2,
        'L' => 3,
        'LEAVE' => 3,
        'R' => 4,
        'REP' => 4,
        'REPLACE' => 4,
        'REPLACEMENT' => 4,
        'M' => 5,
        'MAINT' => 5,
        'MAINTENANCE' => 5,
        'MAINTANANCE' => 5,
        'UNDERMAINTENANCE' => 5,
        'UNDERMAINTANANCE' => 5,
        'I' => 6,
        'INSP' => 6,
        'INSPECTION' => 6,
        'O' => 7,
        'OFF' => 7,
        'HOLIDAY' => 7,
    ];

    /** The date formats a supervisor may use, tried in this order. */
    private const DATE_FORMATS = ['Y-m-d', 'Y/m/d', 'j-n-Y', 'j/n/Y', 'j.n.Y', 'j-n-y', 'j/n/y'];

    /**
     * @return array{
     *     is_attendance: bool,
     *     header: ?string,
     *     date: ?string,
     *     date_token: ?string,
     *     date_error: ?string,
     *     lines: array<int, array{line_no: int, raw: string, vehicle_token: ?string, status_token: ?string, status: ?int, error: ?string}>,
     *     notes: array<int, string>
     * }
     */
    public function parse(string $body, ?CarbonInterface $receivedAt = null): array
    {
        $receivedAt = $receivedAt ? CarbonImmutable::parse($receivedAt) : CarbonImmutable::now();

        $result = [
            'is_attendance' => false,
            'header' => null,
            'date' => null,
            'date_token' => null,
            'date_error' => null,
            'lines' => [],
            'notes' => [],
        ];

        $rawLines = preg_split('/\R/u', $body) ?: [];
        $rawLines = array_values(array_filter(
            array_map(static fn ($line) => trim((string) $line), $rawLines),
            static fn (string $line) => $line !== ''
        ));

        if ($rawLines === []) {
            return $result;
        }

        $header = $this->readHeader($rawLines[0]);

        if ($header === null) {
            return $result;
        }

        $result['is_attendance'] = true;
        $result['header'] = $rawLines[0];
        $result['date_token'] = $header['token'];

        $date = $this->readDate($header['token'], $receivedAt);
        $result['date'] = $date['date'];
        $result['date_error'] = $date['error'];

        foreach (array_slice($rawLines, 1) as $index => $raw) {
            $line = $this->readLine($raw);

            if ($line === null) {
                $result['notes'][] = $raw;

                continue;
            }

            $line['line_no'] = $index + 2; // 1 is the header
            $result['lines'][] = $line;
        }

        return $result;
    }

    /**
     * Strip a vehicle number down to the characters that identify it, so that
     * "jz 9036", "JZ-9036" and "JZ9036" all compare equal. The same function
     * is applied to `vehicles.vehicle_no` when matching, so both sides of the
     * comparison are normalised the same way.
     */
    public static function normalizeVehicleToken(?string $token): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $token)) ?? '';
    }

    /**
     * Does this line start with an attendance keyword, and what follows it?
     *
     * @return ?array{keyword: string, token: string}
     */
    private function readHeader(string $line): ?array
    {
        if (!preg_match('/^([A-Za-z]+)\b[\s:.,-]*(.*)$/u', $line, $matches)) {
            return null;
        }

        $keyword = strtoupper($matches[1]);

        if (!in_array($keyword, self::HEADER_KEYWORDS, true)) {
            return null;
        }

        return ['keyword' => $keyword, 'token' => trim($matches[2])];
    }

    /**
     * The date the attendance is for. An empty token means today, which is the
     * normal case - a supervisor sending the day's attendance that morning.
     *
     * @return array{date: ?string, error: ?string}
     */
    private function readDate(string $token, CarbonImmutable $receivedAt): array
    {
        $token = trim($token);

        if ($token === '') {
            return ['date' => $receivedAt->toDateString(), 'error' => null];
        }

        $upper = strtoupper($token);

        if (in_array($upper, ['TODAY', 'AAJ', 'AJ'], true)) {
            return ['date' => $receivedAt->toDateString(), 'error' => null];
        }

        if (in_array($upper, ['YESTERDAY', 'GUZRA'], true)) {
            return ['date' => $receivedAt->subDay()->toDateString(), 'error' => null];
        }

        // Keep only the part that can be a date, so "12-11-2026 Johar" works.
        if (preg_match('/\d{1,4}[-\/.]\d{1,2}[-\/.]\d{2,4}/', $token, $matches)) {
            $token = $matches[0];
        }

        foreach (self::DATE_FORMATS as $format) {
            // Native DateTimeImmutable rather than Carbon on purpose: Carbon
            // throws on a format that does not fit, and here a format that
            // does not fit is the normal case - the next one is tried instead.
            $native = DateTimeImmutable::createFromFormat('!' . $format, $token);

            if ($native === false) {
                continue;
            }

            // PHP returns false here when the parse was clean, and an array of
            // counts when it was not, so both shapes have to be handled.
            $errors = DateTimeImmutable::getLastErrors();

            if (is_array($errors) && (($errors['error_count'] ?? 0) > 0 || ($errors['warning_count'] ?? 0) > 0)) {
                continue;
            }

            $parsed = CarbonImmutable::instance($native);

            if ($parsed->isAfter($receivedAt->endOfDay())) {
                return [
                    'date' => $parsed->toDateString(),
                    'error' => 'Date is in the future (' . $parsed->format('d-m-Y') . ')',
                ];
            }

            return ['date' => $parsed->toDateString(), 'error' => null];
        }

        return [
            'date' => null,
            'error' => 'Could not read the date "' . $token . '"',
        ];
    }

    /**
     * One attendance line. Returns null when the line is clearly not meant to
     * be attendance at all - "Thanks", "Done", a station name - so chatter at
     * the end of a message does not fill the review screen with errors.
     *
     * @return ?array{raw: string, vehicle_token: ?string, status_token: ?string, status: ?int, error: ?string, line_no: int}
     */
    private function readLine(string $raw): ?array
    {
        // Drop a list marker: "1." or "2)" in front of the vehicle number.
        $line = preg_replace('/^\d{1,2}\s*[.)]\s*/u', '', $raw) ?? $raw;

        // Separators a supervisor might use instead of a space.
        $line = preg_replace('/[,;:|=\x{2013}\x{2014}]+/u', ' ', $line) ?? $line;
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);

        if ($line === '') {
            return null;
        }

        // Chatter, not attendance. Every vehicle number has a digit in it, so
        // a line without one ("Thanks", "Done sir") is a closing pleasantry
        // and is set aside rather than shown as a broken row.
        if (!preg_match('/\d/', $line)) {
            return null;
        }

        $tokens = explode(' ', $line);

        if (count($tokens) >= 2) {
            $statusToken = array_pop($tokens);
            $vehicleToken = implode(' ', $tokens);
        } else {
            // "JZ-9036P" written without a space. The vehicle part has to end
            // in a digit, so a bare word like "PRESENT" is not split up.
            if (!preg_match('/^([A-Za-z0-9][A-Za-z0-9\-\/ ]*\d)\s*(OFF|[PALRMIO])$/iu', $line, $matches)) {
                // A vehicle number on its own: the status was left off.
                return [
                    'raw' => $raw,
                    'vehicle_token' => $line,
                    'status_token' => null,
                    'status' => null,
                    'error' => 'No status code on this line',
                    'line_no' => 0,
                ];
            }

            $vehicleToken = $matches[1];
            $statusToken = $matches[2];
        }

        $status = $this->readStatus($statusToken);

        return [
            'raw' => $raw,
            'vehicle_token' => $vehicleToken,
            'status_token' => $statusToken,
            'status' => $status,
            'error' => $status === null
                ? 'Status "' . $statusToken . '" is not a known code'
                : null,
            'line_no' => 0,
        ];
    }

    /**
     * The `attendance_status.id` a token stands for. A supervisor who types the
     * number instead of the letter is accepted too.
     */
    private function readStatus(string $token): ?int
    {
        $digits = preg_replace('/\D+/', '', $token) ?? '';

        if ($digits !== '' && $digits === $token) {
            $id = (int) $digits;

            return in_array($id, self::STATUS_CODES, true) ? $id : null;
        }

        $letters = preg_replace('/[^A-Z]/', '', strtoupper($token)) ?? '';

        return self::STATUS_CODES[$letters] ?? null;
    }
}
