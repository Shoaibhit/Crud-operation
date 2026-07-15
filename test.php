
<?php

$totalIncome= mysqli_query($conn,"select COALESCE(sum(amount), 0) as total from income");
if($totalIncome){
    $totalIncome=mysqli_fetch_assoc($totalIncome)['total'] ?? 0;
    $Income = $row['total'] ?? 0;
    
}


$sumexpense=mysqli_query($conn, "SELECT COALESCE(sum(amount), 0) as total from expense");
if($sumexpense){
    $totalexpense=mysqli_fetch_asssoc($sumexpense)['total'] ?? 0;
    $expsense =$row[total] ?? 0;
}


$incomequery="SELECT ic.id ,ic.name ,count(ic.id) as entries ,coalesce(sum(amount),0) as total left join income i on i.income_categories ic group by  ic.id,ic.name as ic.name order by ic.name asc"





?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>