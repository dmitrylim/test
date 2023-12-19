
alert('d');
function startNewTournament() {
  $.ajax({
    type: 'GET',
    url: '/start-new-tournament',
      dataType: 'json',
      success: function(data) {
        if (data.code === 200) {

          };
        }
      },
      error: function(xhr, status, error) {
        console.log(error);
      }
        });
    }

startNewTournament();