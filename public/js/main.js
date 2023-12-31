var tournament = localStorage.getItem('tournament');

document.addEventListener('DOMContentLoaded', function() {
    // Check if there is a tournament in localStorage
    if (!tournament) {
        // If no tournament data, call the function to start a new tournament
        startNewTournament();
    }
    else {
        getTournamentInfo();
    }
});

function startNewTournament() {
    $.ajax({
      type: 'GET',
      url: '/start-new-tournament',
      dataType: 'json',
      success: function(data) {
        if (data.code === 200) {
            tournament = data.tournament;
            localStorage.setItem('tournament', tournament);
            $(".tournament-name").html(tournament);
            location.reload();
        }
      },
      error: function(xhr, status, error) {
        console.log(error);
      }
    });
}

function getTournamentInfo() {
    var tournament = localStorage.getItem('tournament');
    $.ajax({
        type: 'GET',
        url: '/get-tournament-info',
        data: { tournament: tournament },
        dataType: 'json',
        success: function (data) {
            if (data.error === 'no') {
                $(".tournament-name").html(data.tournament.name);

                // Check if data.tournament.results and its sub-properties exist
                if (data.tournament.results?.qualifying?.a) {
                    fillTable(data.tournament.results.qualifying.a, document.getElementById('division-a-table'));
                }

                // Check if data.tournament.results and its sub-properties exist
                if (data.tournament.results?.qualifying?.b) {
                    fillTable(data.tournament.results.qualifying.b, document.getElementById('division-b-table'));
                }

                // Check if data.tournament.results exists
                if (data.tournament.results) {
                    fillPlayoffTable(data.tournament.results);
                }
            } else {
                if (data.message === 'no such tournament') {
                    startNewTournament();
                }
            }
        },
        error: function (xhr, status, error) {
            startNewTournament();
            console.log(error);
        }
    });
}

function fillTable(data, table) {
    for (var i = 1; i < table.rows.length; i++) {
        var team = table.rows[i].cells[0].textContent;

        var score = data.score && data.score.find(item => item.team === team);
        table.rows[i].cells[table.rows[i].cells.length - 1].textContent = score ? score.score : '-';
    }

    for (var matchIndex in data) {
        updateTableCells(data[matchIndex], table);
    }
}

function generateDivision(type) {
    $.ajax({
        type: 'GET',
        url: '/generate-division',
        data: { tournament: tournament, type: type },
        dataType: 'json',
        success: function (data) {
            if (data.message == 'Games for this division already exist') {
                return alert('Games for this division already exist');
            }
            if (data.message == 'no such tournament') {
                alert('Tournament not found, creating new');
                startNewTournament();
            }
            if (data.message == 'tournament finished') {
                alert('Tournament finished. Create new to generate division results.');
                startNewTournament();
            }

            if (data.results.a) {
                fillTable(data.results.a, document.getElementById('division-a-table'));
            }

            if (data.results.b) {
                fillTable(data.results.b, document.getElementById('division-b-table'));
            }
        },
        error: function (xhr, status, error) {
            alert('Error');
            console.log(error);
        }
    });
}

function updateTableCells(match,table) {
    var team1 = match.team1;
    var team2 = match.team2;
    var value1 = match.winner === team1 ? '1:0' : '0:1';
    var value2 = match.winner === team2 ? '1:0' : '0:1';

    // Update the cell where team1 is in the vertical direction
    var verticalIndex1, horizontalIndex1;
    for (var i = 1; i < table.rows.length; i++) {
        if (table.rows[i].cells[0].textContent === team1) {
            verticalIndex1 = i;
            break;
        }
    }

    for (var j = 1; j < table.rows[0].cells.length - 1; j++) {
        if (table.rows[0].cells[j].textContent === team2) {
            horizontalIndex1 = j;
            break;
        }
    }

    // Update the cell where team2 is in the vertical direction
    var verticalIndex2, horizontalIndex2;
    for (var k = 1; k < table.rows.length; k++) {
        if (table.rows[k].cells[0].textContent === team2) {
            verticalIndex2 = k;
            break;
        }
    }

    for (var l = 1; l < table.rows[0].cells.length - 1; l++) {
        if (table.rows[0].cells[l].textContent === team1) {
            horizontalIndex2 = l;
            break;
        }
    }

    // Update the cells
    if (verticalIndex1 !== undefined && horizontalIndex1 !== undefined) {
        table.rows[verticalIndex1].cells[horizontalIndex1].textContent = value1;
    }

    if (verticalIndex2 !== undefined && horizontalIndex2 !== undefined) {
        table.rows[verticalIndex2].cells[horizontalIndex2].textContent = value2;
    }
}

function generatePlayoff() {
    $.ajax({
      type: 'GET',
      url: '/generate-playoff',
      data: { tournament: tournament },
      dataType: 'json',
      success: function(data) {
        if (data.tournament) {
            fillPlayoffTable(data.tournament);
        }

        if (data.message == 'Please, generate A and B divisions first') {
            alert('Please, generate A and B divisions first');
        }

        if (data.message == 'no such tournament') {
            alert('Tournament not found, creating new');
            startNewTournament();
        }
        if (data.message == 'tournament finished') {
            alert('Tournament finished. Create new to generate results.');
        }
      },
      error: function(xhr, status, error) {
        alert('Error');
      }
    });
}

function fillPlayoffTable(data) {
    var table = document.getElementById('play-off-table');

    function setCellText(rowIndex, cellIndex, team, winner, isTournamentResults = false) {
        let cell = table.rows[rowIndex].cells[cellIndex];
        let teamLabel = isTournamentResults ? `${team}` : `Team ${team}`;
        cell.textContent = teamLabel;

        if (winner && !isTournamentResults) {
            let score = team === winner ? '1:0' : '0:1';
            cell.textContent += ` ${score}`;
        }
    }

    // Quarterfinals
    setCellText(1, 0, data.quarterFinal[0].team1);
    setCellText(2, 0, data.quarterFinal[0].team2, data.quarterFinal[0].winner);
    
    setCellText(5, 0, data.quarterFinal[1].team1, data.quarterFinal[1].winner);
    setCellText(6, 0, data.quarterFinal[1].team2);

    setCellText(9, 0, data.quarterFinal[2].team1);
    setCellText(10, 0, data.quarterFinal[2].team2, data.quarterFinal[2].winner);

    setCellText(13, 0, data.quarterFinal[3].team1, data.quarterFinal[3].winner);
    setCellText(14, 0, data.quarterFinal[3].team2);

    // Semifinals
    setCellText(3, 1, data.semiFinal[0].team1);
    setCellText(4, 1, data.semiFinal[0].team2, data.semiFinal[0].winner);

    setCellText(7, 1, data.semiFinal[1].team1, data.semiFinal[1].winner);
    setCellText(8, 1, data.semiFinal[1].team2);

    // Finals
    setCellText(5, 2, data.final[0].team1);
    setCellText(6, 2, data.final[0].team2, data.final[0].winner);

    setCellText(9, 2, data.final[1].team1, data.final[1].winner);
    setCellText(10, 2, data.final[1].team2);

    // Tournament results
    for (let i = 2; i <= 5; i++) {
        setCellText(i, 3, `${i-1}. Team ${data.tournamentResults[i - 2]}`, null, true);
    }
}

$(document).ready(function() {

    $('.start-new').on('click', function() {
        startNewTournament();
    });

    $('.generate-division').on('click', function() {
        generateDivision($(this).attr("data-type"));
    });

    $('.generate-playoff').on('click', function() {
        generatePlayoff();
    });
});