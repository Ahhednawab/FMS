<?php

namespace App\Services\WhatsApp;

/**
 * Works out which driver a supervisor meant.
 *
 * A supervisor types "shahinsha" for Shahin shah, "Juneed" for Junaid Ali,
 * "zeshan" for Muhammad Zeeshan and "Shabruk" for Shahrukh. None of those are
 * spelling mistakes exactly - they are how the name sounds, written quickly on
 * a phone - so matching has to be by sound and by word, not character by
 * character.
 *
 * Nothing here decides anything on its own. It returns a score, and the review
 * screen shows the reviewer what the score was based on.
 */
class DriverNameMatcher
{
    /**
     * Words that almost every driver on the list carries, so finding them in
     * common says nothing. "Muhammad Nadeem" and "Muhammad Akram" share a word
     * but are not the same man.
     */
    private const COMMON_WORDS = ['muhammad', 'mohammad', 'mohammed', 'md', 'syed', 'mr', 'sahib', 'bhai'];

    /** Below this, a match is not worth offering at all. */
    public const FLOOR = 0.55;

    /** At or above this, the match is good enough to fill in without asking. */
    public const CONFIDENT = 0.78;

    /**
     * Rank candidates against the name as it was written.
     *
     * @param  iterable<object>  $candidates  anything with id and full_name
     * @return array<int, array{id: int, name: string, score: float}> best first
     */
    public function rank(string $written, iterable $candidates, int $limit = 5): array
    {
        $written = trim($written);

        if ($written === '') {
            return [];
        }

        $scored = [];

        foreach ($candidates as $candidate) {
            $score = $this->score($written, (string) $candidate->full_name);

            if ($score >= self::FLOOR) {
                $scored[] = [
                    'id' => (int) $candidate->id,
                    'name' => (string) $candidate->full_name,
                    'score' => $score,
                ];
            }
        }

        usort($scored, static fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * How alike are these two names, 0 to 1?
     *
     * The written name is usually shorter than the recorded one - a supervisor
     * writes "Rahat" where the register says "Rahat Ullah" - so the score asks
     * how well the written words are accounted for, not whether the two names
     * are the same length.
     */
    public function score(string $written, string $recorded): float
    {
        $writtenWords = $this->words($written);
        $recordedWords = $this->words($recorded);

        if ($writtenWords === [] || $recordedWords === []) {
            return 0.0;
        }

        // Run together, "shahinsha" and "Shahin shah" are the same letters in
        // the same order, which no word-by-word comparison would catch.
        $squashedWritten = implode('', $writtenWords);
        $squashedRecorded = implode('', $recordedWords);

        if ($squashedWritten === $squashedRecorded) {
            return 1.0;
        }

        $best = 0.0;
        $accountedFor = [];

        foreach ($writtenWords as $word) {
            $wordBest = 0.0;
            $bestIndex = null;

            foreach ($recordedWords as $index => $against) {
                $score = $this->compareWords($word, $against);

                if ($score > $wordBest) {
                    $wordBest = $score;
                    $bestIndex = $index;
                }
            }

            if ($bestIndex !== null && $wordBest >= 0.8) {
                $accountedFor[$bestIndex] = true;
            }

            $best += $wordBest;
        }

        $wordScore = $best / count($writtenWords);

        // Matching one word of a longer name is a weaker claim than matching
        // the whole of it. Without this "Umair" scores the same against
        // "Umair" and "Umair-uddin", and the two cannot be told apart.
        // Supervisors do drop surnames, so the penalty stays small.
        $coverage = count($accountedFor) / count($recordedWords);
        $wordScore *= 0.85 + (0.15 * $coverage);

        // The run-together comparison catches names split differently, as in
        // "shahinsha" for "Shahin shah". It is only trusted between names of
        // comparable length: without that, "Imran akhtr" run together starts
        // with "Imran", and a bare "Imran" in the register beats the Imran
        // Akhtar the supervisor actually meant.
        $shorter = min(strlen($squashedWritten), strlen($squashedRecorded));
        $longer = max(strlen($squashedWritten), strlen($squashedRecorded));
        $comparable = $longer > 0 && ($shorter / $longer) >= 0.6;

        $squashScore = $comparable
            ? $this->compareWords($squashedWritten, $squashedRecorded)
            : 0.0;

        return round(max($wordScore, $squashScore), 4);
    }

    /**
     * One word against one word, by sound first and spelling second.
     *
     * metaphone() is what turns "Zeeshan" and "zeshan", or "Shahzad" and
     * "shezad", into the same key. Where it does not, similar_text still
     * rewards a near miss.
     */
    private function compareWords(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }

        $short = strlen($a) <= strlen($b) ? $a : $b;
        $long = strlen($a) <= strlen($b) ? $b : $a;

        // "Rahat" at the front of "Rahatullah" is the usual shortening, and
        // the more of the longer word it covers the stronger the claim.
        // It has to be the front: "shah" turns up inside half the register,
        // and treating that as a match pulls unrelated names together.
        if (strlen($short) >= 4 && str_starts_with($long, $short)) {
            return 0.86 + (0.09 * (strlen($short) / strlen($long)));
        }

        // Buried inside rather than at the front is weaker, and needs a longer
        // fragment before it counts for anything.
        if (strlen($short) >= 5 && str_contains($long, $short)) {
            return 0.84;
        }

        $soundA = metaphone($a);
        $soundB = metaphone($b);

        // Sound is only allowed to speak where the names start alike. Every
        // phonetic spelling in the real registers keeps its first sound -
        // zeshan/Zeeshan, shezad/Shahzad, Juneed/Junaid - while metaphone on
        // its own flattens short names until "Saud" and "Zada" look identical,
        // which is how a driver gets marked present under the wrong name.
        $soundsUsable = $soundA !== ''
            && $soundB !== ''
            && strlen($short) >= 4
            && $a[0] === $b[0];

        if ($soundsUsable && $soundA === $soundB) {
            return 0.9;
        }

        similar_text($a, $b, $percent);
        $letters = $percent / 100;

        if ($soundsUsable) {
            similar_text($soundA, $soundB, $soundPercent);
            $sound = $soundPercent / 100;

            // Spelling is what the supervisor was guessing at, so sound counts
            // for as much - but no more, or short names start matching anything.
            return max($letters, ($sound * 0.5) + ($letters * 0.5));
        }

        return $letters;
    }

    /**
     * A name split into the words worth comparing.
     *
     * @return array<int, string>
     */
    private function words(string $name): array
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z ]+/', ' ', $name) ?? $name;

        $words = array_values(array_filter(
            preg_split('/\s+/', trim($name)) ?: [],
            static fn (string $word) => $word !== ''
        ));

        $meaningful = array_values(array_filter(
            $words,
            static fn (string $word) => !in_array($word, self::COMMON_WORDS, true) && strlen($word) > 1
        ));

        // If a name is nothing but common words, keep them - something has to
        // be compared, and "Muhammad" on its own is still what was written.
        return $meaningful === [] ? $words : $meaningful;
    }
}
