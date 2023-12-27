<?php

trait WinnerAssertionTrait
{
    public function assertStageWinners(array $gameResults, array $tournamentResults): void
    {
        $stages = ['quarterFinal', 'semiFinal', 'final'];

        foreach ($stages as $stage) {
            $this->assertTrue($this->isStageWinner($gameResults[$stage], $tournamentResults[0]));

            // Check if the second team was the winner in all stages except final
            if ($stage !== 'final') {
                $this->assertTrue($this->isStageWinner($gameResults[$stage], $tournamentResults[1]));
            } else {
                $this->assertFalse($this->isStageWinner($gameResults[$stage], $tournamentResults[1]));
            }
        }
    }

    private function isStageWinner(array $stageResults, string $winner): bool
    {
        foreach ($stageResults as $stage) {
            // Check if the winner was the winner in either "team1" or "team2"
            if ($stage['team1'] === $winner || $stage['team2'] === $winner) {
                // Ensure the winner is the same as the specified winner
                if ($stage['winner'] !== $winner) {
                    return false;
                }
            }
        }

        return true;
    }
}