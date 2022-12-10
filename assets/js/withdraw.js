const env = {
  // apiUrl: 'https://game-live.fun',
  // apiUrl: 'http://localhost/apps/game-live-app',
  apiUrl: 'http://localhost/game',
}

let session = localStorage.getItem('session');

let tableData = document.getElementById('withdraw-history')

tableData.innerHTML = `spinner`

async function withdraw() {
  let list = []
  await axios({
    method: 'post',
    url: `${env.apiUrl}/withdrawal_fetch.php`,
    data: {
      userid: session
    }
  }).then((res) => {
    if (res.data.success == 1) {
      list = res.data.list;
      if (list.length > 0) {
        tableData.innerHTML = list.map((item) => {
          return (
            `
              <tr>
                <th>${item.withdrawid}</th>
                <th>${item.userid}</th>
                <th>${item.withdrawreqtime}</th>
                <th>Remaining Balance</th>
                <th>Withdraw Amount</th>
                <th>Number</th>
                <th>Payment Mode</th>
                <th>Withdraw Status</th>
                <th>Account Number</th>
                <th>Account Name</th>
                <th>Bank Name</th>
                <th>IFSC</th>
                <th>Account Type</th>
              </tr>
            `
          )
        })
      } else {
        tableData.innerHTML = "No data found!!"
      }
    }
  }).catch((err) => {
    tableData.innerHTML = "Something went wrong!"
  });
}

if (session) {
  withdraw();
}