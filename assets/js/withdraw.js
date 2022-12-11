const env = {
   apiUrl: 'https://newkhel.in',
  // apiUrl: 'http://localhost/apps/game-live-app',
  // apiUrl: 'http://localhost/game',
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
                <th>${item.remainingbalance}</th>
                <th>${item.withdrawamount}</th>
                <th>${item.mobile}</th>
                <th>${item.paymentmode}</th>
                <th>${item.withdrawstatus}</th>
                <th>${item.accountnumber}</th>
                <th>${item.accountname}</th>
                <th>${item.bankname}</th>
                <th>${item.ifsc}</th>
                <th>${item.accounttype}</th>
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