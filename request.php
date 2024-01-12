<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Maker</title>
    <style>
    body {
        background-color: #1a1a1a;
        color: #fff;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        height: 100vh;
    }

    form {
        padding: 10px;
        margin: 10px;
        width: 300px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: #dddddd;
        text-align: center;
    }

    input[type="text"],
    input[type="submit"],
    textarea {
        margin-bottom: 10px;
        padding: 5px;
        border: none;
        background-color: #333;
        color: #fff;
    }

    input[type="submit"] {
        background-color: #007acc;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #005d9e;
    }

    textarea {
        resize: none;
        height: 100px;
        background-color: #333;
        color: #fff;
    }

    textarea:disabled {
        background-color: #300000;
    }

    pre {
        background-color: #333;
        width: fit-content;
        height: auto;
        color: #00ff00;
        padding: 10px;
        overflow: auto;
        font-family: 'Courier New', monospace;
    }

    select {
        margin-bottom: 10px;
        padding: 5px;
        background-color: #333;
        color: #fff;
        -webkit-appearance: none;
    }

    option {
        background-color: #333;
        border-radius: 0;
        color: #fff;
    }
    </style>
</head>

<body>
    <form action="request.php" method="POST">
        <label>Request Maker</label>
        <input type="text" name="website" value="http://localhost/myapi" required>
        <select name="method" onChange="showOther()" required>
            <option value="GET" selected>GET</option>
            <option value="POST">POST</option>
            <option value="PATCH">PATCH</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
        </select>
        <textarea name="body" placeholder="{ 'Key': 'Value' }" disabled></textarea>
        <input type="submit" value="Send Request">
    </form>

    <script type="text/javascript">
    function showOther() {
        var method = document.getElementsByName("method")[0].value;
        console.log(method)
        if (method == "GET" || method == "DELETE") {
            document.getElementsByName("body")[0].disabled = true;
        } else {
            document.getElementsByName("body")[0].disabled = false;
        }
    }
    </script>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $website = isset($_POST["website"]) ? $_POST["website"] : '';
            $method = isset($_POST["method"]) ? $_POST["method"] : '';

            if (!empty($website) && !empty($method)) {
                $response = curlRequest($website, $method);
                echo "<label>Output</label>";
                echo "<pre>$response</pre>";
            } else {
                echo "Invalid input. Please provide both website and method.";
            }
        }

        function curlRequest($url, $method) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            if ($method != "GET" && $method != "DELETE") {
                $body = isset($_POST["body"]) ? $_POST["body"] : '';
                $decodedBody = json_decode($body, true);
                $jsonBody = json_encode($decodedBody);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody);
            }

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                echo 'Curl error: ' . curl_error($curl);
            }

            curl_close($curl);
            return $response;
        }
    ?>
</body>

</html>