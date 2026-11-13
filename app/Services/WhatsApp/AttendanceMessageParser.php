<?php

namespace App\Services\WhatsApp;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeImmutable;

/**
 * Reads a supervisor WhatsApp message and works out what it says.
 *
 * Written against what supervisors actually send rather than a format anyone
 * was asked to adopt. Three shapes appear in the real groups:
 *
 *   A  Orengi & garden driver's attendence
 *      1•JZ 9785 shahinsha (P)
 *      13•LE 1963 Nizam (A) Imran akhtr (P)
 *
 *   B  30=Jz-9641  per rehman
 *      33=Jz-9744  Gachi (L)Shaid hussain
 *
 *   C  12- NOV -2026 KORANGI
 *      VEHICLES ATTENDANCE
 *      1= Jz 9673 (P)
 *      14=Jz-9670  (Day)
 *
 * The statuses are usually the emoji 🅿️ and 🅰️ rather than letters, the index
 * separator is any of • = . ), the vehicle number may be spaced, dashed or
 * dotted, and format B carries no status at all.
 *
 * Deliberately knows nothing about the database: it turns text into structured
 * lines and stops there, so every rule in it can be tested without a schema.
 * Working out which driver a name belongs to is AttendanceDraftBuilder.
 */
class AttendanceMessageParser
{
    /** What kind of register a message is. */
    public const KIND_DRIVER = 'driver';
    public const KIND_VEHICLE = 'vehicle';

    /**
     * Status marker to `attendance_status.id`.
     *
     * The emoji are what WhatsApp actually carries: 🅿️ is U+1F17F and 🅰️ is
     * U+1F170, each usually followed by a variation selector.
     */
    public const STATUS_CODES = [
        'P' => 1,
        'PRESENT' => 1,
        'A' => 2,
        'ABSENT' => 2,
        'L' => 3,
        'LEAVE' => 3,
        'R' => 4,
        'REPLACE' => 4,
        'REPLACEMENT' => 4,
        'REPLECMENT' => 4,
        'M' => 5,
        'MAINT' => 5,
        'MAINTENANCE' => 5,
        'MAINTANANCE' => 5,
        'WORKSHOP' => 5,
        'I' => 6,
        'INSP' => 6,
        'INSPECTION' => 6,
        'O' => 7,
        'OFF' => 7,
        'HOLIDAY' => 7,
    ];

    /** Bracketed words that name a shift rather than a status. */
    public const SHIFT_WORDS = ['DAY', 'NIGHT', 'MORNING', 'EVENING', 'GENERAL'];

    /** The emoji WhatsApp carries for P and A, with their optional selectors. */
    private const EMOJI_P = "\u{1F17F}";
    private const EMOJI_A = "\u{1F170}";

    /** A vehicle number: two letters, then 3-4 digits, however they are spaced. */
    private const VEHICLE_PATTERN = '/\b([A-Z]{2})\s*[-.\s]?\s*(\d{3,4})\b/iu';

    /** The date formats a supervisor may use, tried in this order. */
    private const DATE_FORMATS = ['Y-m-d', 'Y/m/d', 'j-n-Y', 'j/n/Y', 'j.n.Y', 'j-n-y', 'j/n/y'];

    /**
     * @return array{
     *     is_attendance: bool,
     *     kind: string,
     *     header: ?string,
     *     date: ?string,
     *     date_token: ?string,
     *     date_error: ?string,
     *     lines: array<int, array<string, mixed>>,
     *     notes: array<int, string>
     * }
     */
    public function parse(string $body, ?CarbonInterface $receivedAt = null): array
    {
        $receivedAt = $receivedAt ? CarbonImmutable::parse($receivedAt) : CarbonImmutable::now();

        $result = [
            'is_attendance' => false,
            'kind' => self::KIND_DRIVER,
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

        $lineNumbers = [];
        $parsedLines = [];

        foreach ($rawLines as $index => $raw) {
            $line = $this->readLine($raw);

            if ($line === null) {
                $result['notes'][] = $raw;

                continue;
            }

            $line['line_no'] = $index + 1;
            $parsedLines[] = $line;
            $lineNumbers[] = $index;
        }

        // No keyword is required, because the real messages do not use one.
        // A message is attendance when it is mostly a list of vehicle numbers,
        // which no ordinary chat message ever is.
        if (count($parsedLines) < 3) {
            return $result;
        }

        $result['is_attendance'] = true;
        $result['lines'] = $parsedLines;

        // Anything before the first vehicle line is the header.
        $headerLines = array_slice($rawLines, 0, $lineNumbers[0]);
        $result['header'] = $headerLines === [] ? null : implode(' ', $headerLines);

        // The header is chatter only in the sense that it is not a vehicle line;
        // it still says what the message is, so it is not kept as a note.
        $result['notes'] = array_values(array_filter(
            $result['notes'],
            static fn (string $note) => !in_array($note, $headerLines, true)
        ));

        $result['kind'] = $this->readKind($result['header'], $parsedLines);

        $date = $this->readDate($result['header'], $receivedAt);
        $result['date'] = $date['date'];
        $result['date_token'] = $date['token'];
        $result['date_error'] = $date['error'];

        return $result;
    }

    /**
     * Strip a vehicle number down to the characters that identify it, so that
     * "jz 9036", "JZ-9036", "Jz.9036" and "JZ9036" all compare equal. The same
     * function is applied to `vehicles.vehicle_no` when matching, so both sides
     * of the comparison are normalised the same way.
     */
    public static function normalizeVehicleToken(?string $token): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $token)) ?? '';
    }

    /**
     * One line of the register.
     *
     * Returns null when the line holds no vehicle number at all - a header, a
     * station name, or a closing "Thanks sir" - so those do not turn into rows
     * that look broken.
     *
     * @return ?array<string, mixed>
     */
    private function readLine(string $raw): ?array
    {
        // Drop the index: "1•", "37=", "23.", "4)" and so on.
        $line = preg_replace('/^\s*\d{1,3}\s*[•·.=)\-:]\s*/u', '', $raw) ?? $raw;
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);

        if ($line === '' || !preg_match(self::VEHICLE_PATTERN, $line, $vehicle, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $vehicleToken = $vehicle[0][0];
        $tail = trim(substr($line, $vehicle[0][1] + strlen($vehicleToken)));

        // A second vehicle number is a replacement vehicle, as in
        // "Jz 9853 inspection replecment LE-1940 (P)".
        $secondVehicle = null;
        if (preg_match(self::VEHICLE_PATTERN, $tail, $next, PREG_OFFSET_CAPTURE)) {
            $secondVehicle = $next[0][0];
            $tail = trim(substr($tail, 0, $next[0][1]) . ' ' . substr($tail, $next[0][1] + strlen($secondVehicle)));
        }

        $shift = $this->readShift($tail);
        if ($shift !== null) {
            $tail = trim(str_ireplace('(' . $shift . ')', '', $tail));
        }

        return [
            'raw' => $raw,
            'line_no' => 0,
            'vehicle_token' => $vehicleToken,
            'second_vehicle_token' => $secondVehicle,
            'shift' => $shift,
            'people' => $this->readPeople($tail),
            'keywords' => $this->readKeywords($tail),
            'tail' => $tail,
            'error' => null,
        ];
    }

    /**
     * Split what follows the vehicle number into the people it names and the
     * status each one carries.
     *
     * The rule is uniform: a status marker applies to the text in front of it.
     * So "Nizam 🅰️ Imran akhtr 🅿️" is two people, and "Gachi (L)Shaid hussain"
     * is Gachi on leave followed by the man who stood in for him.
     *
     * A bare letter only counts as a status at the very end of the line. That
     * is what keeps "A waseem & Habib 🅿️" as one name rather than reading the
     * leading initial as Absent.
     *
     * @return array<int, array{name: string, status_token: ?string, status: ?int}>
     */
    private function readPeople(string $tail): array
    {
        $people = [];
        $buffer = '';
        $length = mb_strlen($tail);
        $i = 0;

        while ($i < $length) {
            // A bare letter is only a status when it stands as its own word.
            // Without this the final "r" of "Umair" reads as Replace and the
            // name silently loses its last letter.
            $atWordStart = $i === 0
                || preg_match('/[\s(),\/|&.\-]/u', mb_substr($tail, $i - 1, 1)) === 1;

            $marker = $this->readMarkerAt(mb_substr($tail, $i), $atWordStart);

            if ($marker === null) {
                $buffer .= mb_substr($tail, $i, 1);
                $i++;

                continue;
            }

            $people[] = [
                'name' => $this->cleanName($buffer),
                'status_token' => $marker['token'],
                'status' => $marker['status'],
            ];

            $buffer = '';
            $i += $marker['length'];
        }

        $trailing = $this->cleanName($buffer);

        if ($trailing !== '') {
            // Text after the last marker, or a line with no marker at all.
            $people[] = ['name' => $trailing, 'status_token' => null, 'status' => null];
        }

        return $people;
    }

    /**
     * Is there a status marker at the start of this text?
     *
     * @return ?array{token: string, status: int, length: int}
     */
    private function readMarkerAt(string $rest, bool $atWordStart): ?array
    {
        // The emoji WhatsApp actually sends, with any variation selector.
        foreach ([self::EMOJI_P => 'P', self::EMOJI_A => 'A'] as $emoji => $letter) {
            if (str_starts_with($rest, $emoji)) {
                $length = 1;
                if (mb_substr($rest, 1, 1) === "\u{FE0F}") {
                    $length = 2;
                }

                return ['token' => $letter, 'status' => self::STATUS_CODES[$letter], 'length' => $length];
            }
        }

        // A bracketed marker: "(P)", "( L)", and the unclosed "(P" people type.
        if (preg_match('/^\(\s*([A-Za-z]{1,12})\s*\)?/u', $rest, $matches)) {
            $word = strtoupper($matches[1]);

            if (isset(self::STATUS_CODES[$word])) {
                return [
                    'token' => $word,
                    'status' => self::STATUS_CODES[$word],
                    'length' => mb_strlen($matches[0]),
                ];
            }
        }

        // A bare word, but only where the line ends - the anchored $ is what
        // does that, and it is what keeps the initial in "A waseem & Habib"
        // from being read as Absent.
        if ($atWordStart && preg_match('/^([A-Za-z]{1,12})\s*$/u', $rest, $matches)) {
            $word = strtoupper($matches[1]);

            if (isset(self::STATUS_CODES[$word])) {
                return [
                    'token' => $word,
                    'status' => self::STATUS_CODES[$word],
                    'length' => mb_strlen($matches[0]),
                ];
            }
        }

        return null;
    }

    /** A bracketed shift word, which is not a status. */
    private function readShift(string $tail): ?string
    {
        if (!preg_match('/\(\s*([A-Za-z]+)\s*\)/u', $tail, $matches)) {
            return null;
        }

        $word = strtoupper($matches[1]);

        return in_array($word, self::SHIFT_WORDS, true) ? ucfirst(strtolower($word)) : null;
    }

    /**
     * Words that say something about the vehicle rather than the driver.
     *
     * @return array<int, string>
     */
    private function readKeywords(string $tail): array
    {
        $found = [];

        foreach (['inspection', 'replecment', 'replacement', 'workshop', 'maintenance'] as $keyword) {
            if (stripos($tail, $keyword) !== false) {
                $found[] = $keyword;
            }
        }

        return $found;
    }

    /**
     * Does this name actually hold two people? "A waseem & Habib" is two men
     * sharing a vehicle, and picking one of them would be a coin toss.
     */
    public static function namesTwoPeople(string $name): bool
    {
        return preg_match('/\s(&|and)\s/i', $name) === 1;
    }

    /** Tidy the text left over between markers so it reads as a name. */
    private function cleanName(string $name): string
    {
        $name = preg_replace('/[(),.\-\/|:;]+/u', ' ', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * Driver register or vehicle register? The vehicle groups say so in their
     * header; otherwise a message whose lines carry no names at all is also a
     * vehicle list, since a driver register always names somebody.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function readKind(?string $header, array $lines): string
    {
        if ($header !== null && preg_match('/\bvehicles?\b/i', $header)) {
            return self::KIND_VEHICLE;
        }

        $named = 0;

        foreach ($lines as $line) {
            foreach ($line['people'] as $person) {
                if ($person['name'] !== '') {
                    $named++;

                    break;
                }
            }
        }

        return $named === 0 ? self::KIND_VEHICLE : self::KIND_DRIVER;
    }

    /**
     * The date the register is for, read out of the header.
     *
     * Only the vehicle groups put one there; a driver register normally has
     * none, and then the day the message arrived is used.
     *
     * @return array{date: ?string, token: ?string, error: ?string}
     */
    private function readDate(?string $header, CarbonImmutable $receivedAt): array
    {
        if ($header === null || trim($header) === '') {
            return ['date' => $receivedAt->toDateString(), 'token' => null, 'error' => null];
        }

        // "12- NOV -2026", "12 Nov 2026"
        if (preg_match('/\b(\d{1,2})\s*[-.\/ ]\s*([A-Za-z]{3,9})\s*[-.\/ ]\s*(\d{2,4})\b/u', $header, $matches)) {
            $token = $matches[0];
            $attempt = $this->fromParts($matches[1], $matches[2], $matches[3]);

            if ($attempt !== null) {
                return $this->checkFuture($attempt, $token, $receivedAt);
            }
        }

        // "12-11-2026", "2026-11-12"
        if (preg_match('/\b\d{1,4}[-\/.]\d{1,2}[-\/.]\d{2,4}\b/u', $header, $matches)) {
            $token = $matches[0];

            foreach (self::DATE_FORMATS as $format) {
                $native = DateTimeImmutable::createFromFormat('!' . $format, $token);

                if ($native === false) {
                    continue;
                }

                $errors = DateTimeImmutable::getLastErrors();

                if (is_array($errors) && (($errors['error_count'] ?? 0) > 0 || ($errors['warning_count'] ?? 0) > 0)) {
                    continue;
                }

                return $this->checkFuture(CarbonImmutable::instance($native), $token, $receivedAt);
            }

            return [
                'date' => null,
                'token' => $token,
                'error' => 'Could not read the date "' . $token . '"',
            ];
        }

        return ['date' => $receivedAt->toDateString(), 'token' => null, 'error' => null];
    }

    /** Build a date from a day, a month name and a year. */
    private function fromParts(string $day, string $monthName, string $year): ?CarbonImmutable
    {
        $native = DateTimeImmutable::createFromFormat('!j-M-Y', $day . '-' . substr($monthName, 0, 3) . '-' . $year);

        if ($native === false) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();

        if (is_array($errors) && (($errors['error_count'] ?? 0) > 0 || ($errors['warning_count'] ?? 0) > 0)) {
            return null;
        }

        return CarbonImmutable::instance($native);
    }

    /**
     * A date after today is kept but flagged - it is nearly always a typo in
     * the month, and silently accepting it would date a whole register wrong.
     *
     * @return array{date: ?string, token: ?string, error: ?string}
     */
    private function checkFuture(CarbonImmutable $parsed, string $token, CarbonImmutable $receivedAt): array
    {
        if ($parsed->isAfter($receivedAt->endOfDay())) {
            return [
                'date' => $parsed->toDateString(),
                'token' => $token,
                'error' => 'Date is in the future (' . $parsed->format('d-m-Y') . ')',
            ];
        }

        return ['date' => $parsed->toDateString(), 'token' => $token, 'error' => null];
    }
}
