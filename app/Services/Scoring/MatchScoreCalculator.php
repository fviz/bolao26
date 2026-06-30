<?php

namespace App\Services\Scoring;

class MatchScoreCalculator
{
    public function calculate(
        bool $isKnockout,
        bool $wentToPenalties,
        ?string $actualPenaltyWinner,
        int $actualHome,
        int $actualAway,
        int $predictedHome,
        int $predictedAway,
        ?string $predictedPenaltyWinner,
    ): int {
        if ($isKnockout && $wentToPenalties && $predictedHome === $predictedAway) {
            $penaltyPoints = $this->knockoutPenaltyPoints(
                $actualHome,
                $actualAway,
                $predictedHome,
                $predictedAway,
                $actualPenaltyWinner,
                $predictedPenaltyWinner,
            );

            if ($penaltyPoints !== null) {
                return $penaltyPoints;
            }
        }

        return $this->regularTimePoints(
            $actualHome,
            $actualAway,
            $predictedHome,
            $predictedAway,
        );
    }

    private function knockoutPenaltyPoints(
        int $actualHome,
        int $actualAway,
        int $predictedHome,
        int $predictedAway,
        ?string $actualPenaltyWinner,
        ?string $predictedPenaltyWinner,
    ): ?int {
        if ($actualHome !== $actualAway) {
            return null;
        }

        if ($actualPenaltyWinner === null || $predictedPenaltyWinner === null) {
            return null;
        }

        $penaltyWinnerCorrect = $actualPenaltyWinner === $predictedPenaltyWinner;

        if ($predictedHome === $actualHome && $predictedAway === $actualAway && $penaltyWinnerCorrect) {
            return 220;
        }

        if ($penaltyWinnerCorrect) {
            return 120;
        }

        if ($predictedHome === $actualHome && $predictedAway === $actualAway) {
            return 200;
        }

        return 100;
    }

    private function regularTimePoints(
        int $actualHome,
        int $actualAway,
        int $predictedHome,
        int $predictedAway,
    ): int {
        if ($predictedHome === $actualHome && $predictedAway === $actualAway) {
            return 200;
        }

        $predictedOutcome = $this->outcome($predictedHome, $predictedAway);
        $actualOutcome = $this->outcome($actualHome, $actualAway);

        if ($predictedOutcome === 0 && $actualOutcome === 0) {
            return 100;
        }

        if ($predictedOutcome !== 0 && $predictedOutcome === $actualOutcome) {
            $homeGoalsMatch = $predictedHome === $actualHome;
            $awayGoalsMatch = $predictedAway === $actualAway;

            if ($homeGoalsMatch || $awayGoalsMatch) {
                return 95;
            }

            return 75;
        }

        if ($predictedHome === $actualHome || $predictedAway === $actualAway) {
            return 20;
        }

        return 0;
    }

    /**
     * -1 = home win, 0 = draw, 1 = away win
     */
    private function outcome(int $home, int $away): int
    {
        if ($home > $away) {
            return -1;
        }

        if ($home < $away) {
            return 1;
        }

        return 0;
    }
}
