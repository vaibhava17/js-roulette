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
                <td>${item.withdrawid}</td>
                <td>${item.userid}</td>
                <td>${item.withdrawreqtime}</td>
                <td>${item.remainingbalance}</td>
                <td>${item.withdrawamount}</td>
                <td>${item.mobile}</td>
                <td>${item.paymentmode}</td>
                <td>${item.withdrawstatus}</td>
                <td>${item.accountnumber}</td>
                <td>${item.accountname}</td>
                <td>${item.bankname}</td>
                <td>${item.ifsc}</td>
                <td>${item.accounttype}</td>
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