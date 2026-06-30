<?php

use App\Services\Scoring\MatchScoreCalculator;
use App\Support\PenaltyWinner;

beforeEach(function () {
    $this->calculator = new MatchScoreCalculator;
});

test('exact score awards 200 points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 2,
        actualAway: 1,
        predictedHome: 2,
        predictedAway: 1,
        predictedPenaltyWinner: null,
    ))->toBe(200);
});

test('correct draw not exact awards 100 points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 2,
        actualAway: 2,
        predictedHome: 1,
        predictedAway: 1,
        predictedPenaltyWinner: null,
    ))->toBe(100);
});

test('correct winner and one team goals awards 95 points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 3,
        actualAway: 1,
        predictedHome: 3,
        predictedAway: 0,
        predictedPenaltyWinner: null,
    ))->toBe(95);
});

test('correct winner only awards 75 points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 1,
        actualAway: 0,
        predictedHome: 3,
        predictedAway: 1,
        predictedPenaltyWinner: null,
    ))->toBe(75);
});

test('one team goals exact awards 20 points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 1,
        actualAway: 0,
        predictedHome: 1,
        predictedAway: 2,
        predictedPenaltyWinner: null,
    ))->toBe(20);
});

test('knockout perfect penalty prediction awards 220 points', function () {
    expect($this->calculator->calculate(
        isKnockout: true,
        wentToPenalties: true,
        actualPenaltyWinner: PenaltyWinner::Away,
        actualHome: 2,
        actualAway: 2,
        predictedHome: 2,
        predictedAway: 2,
        predictedPenaltyWinner: PenaltyWinner::Away,
    ))->toBe(220);
});

test('knockout good penalty prediction awards 120 points', function () {
    expect($this->calculator->calculate(
        isKnockout: true,
        wentToPenalties: true,
        actualPenaltyWinner: PenaltyWinner::Away,
        actualHome: 3,
        actualAway: 3,
        predictedHome: 2,
        predictedAway: 2,
        predictedPenaltyWinner: PenaltyWinner::Away,
    ))->toBe(120);
});

test('knockout exact score wrong penalty winner awards 200 points', function () {
    expect($this->calculator->calculate(
        isKnockout: true,
        wentToPenalties: true,
        actualPenaltyWinner: PenaltyWinner::Home,
        actualHome: 2,
        actualAway: 2,
        predictedHome: 2,
        predictedAway: 2,
        predictedPenaltyWinner: PenaltyWinner::Away,
    ))->toBe(200);
});

test('knockout tie wrong penalty winner awards 100 points', function () {
    expect($this->calculator->calculate(
        isKnockout: true,
        wentToPenalties: true,
        actualPenaltyWinner: PenaltyWinner::Home,
        actualHome: 2,
        actualAway: 2,
        predictedHome: 1,
        predictedAway: 1,
        predictedPenaltyWinner: PenaltyWinner::Away,
    ))->toBe(100);
});

test('knockout without penalties uses regular scoring', function () {
    expect($this->calculator->calculate(
        isKnockout: true,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 2,
        actualAway: 1,
        predictedHome: 2,
        predictedAway: 1,
        predictedPenaltyWinner: null,
    ))->toBe(200);
});

test('no match awards zero points', function () {
    expect($this->calculator->calculate(
        isKnockout: false,
        wentToPenalties: false,
        actualPenaltyWinner: null,
        actualHome: 2,
        actualAway: 0,
        predictedHome: 0,
        predictedAway: 3,
        predictedPenaltyWinner: null,
    ))->toBe(0);
});
