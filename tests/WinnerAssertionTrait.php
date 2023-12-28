<?php

trait WinnerAssertionTrait
{
    public function assertQuarterFinalTeams($teamsA, $teamsB, $result)
    {
        $team1 = $teamsA[0]->getTeam()->getName();
        $team2 = $teamsB[3]->getTeam()->getName();
        $this->assertEquals($team1, $result['quarterFinal'][0]['team1']);
        $this->assertEquals($team2, $result['quarterFinal'][0]['team2']);

        $team1 = $teamsA[3]->getTeam()->getName();
        $team2 = $teamsB[0]->getTeam()->getName();
        $this->assertEquals($team1, $result['quarterFinal'][3]['team1']);
        $this->assertEquals($team2, $result['quarterFinal'][3]['team2']);
    }

    public function assertSemiFinalTeams($result)
    {
        $this->assertEquals($result['semiFinal'][0]['team1'], $result['quarterFinal'][0]['winner']);
        $this->assertEquals($result['semiFinal'][0]['team2'], $result['quarterFinal'][1]['winner']);
    }

    public function assertFinalTeams($result)
    {
        $this->assertEquals($result['final'][0]['team1'], $result['semiFinal'][0]['winner']);
        $this->assertEquals($result['final'][0]['team2'], $result['semiFinal'][1]['winner']);
    }

    public function assertThirdPlace($result)
    {
        $this->assertEquals($result['tournamentResults'][2], $result['final'][1]['winner']);
    }
    
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