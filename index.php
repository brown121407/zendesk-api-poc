<?php
include './zendesk.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

<?php

$fieldNames = ['subject', 'body', 'firstName', 'lastName', 'email', 'tag'];
foreach ($fieldNames as $fieldName) {
    if (empty($_POST[$fieldName])) {
        die('Invalid form. Missing ' . $fieldName);
    }
}

$zendesk = new Zendesk\API();
$ticket = (new Zendesk\Ticket())
    ->setSubject($_POST['subject'])
    ->setBody($_POST['body'])
    ->setRequester($_POST['firstName'] . ' ' . $_POST['lastName'], $_POST['email'])
    ->setTags([$_POST['tag']]);

echo $zendesk->createTicket($ticket);
?>

<?php else: ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Zendesk Test</title>
        <style>
            form, form > div {
                display: flex;
                flex-direction: column;
                align-items: start;
            }

            form {
                gap: 2rem;
            }
        </style>
    </head>
    <body>
        <form action="/" method="post">
            <h1>Contact</h1>

            <div>
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="firstName">
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="lastName">
            </div>

            <div>
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </div>

            <div>
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject">
            </div>

            <div>
                <label for="body">Body</label>
                <input type="text" id="body" name="body">
            </div>

            <div>
                <label for="tag">Tag</label>
                <select id="tag" name="tag">
                    <?php foreach (Zendesk\Tag::ALL as $tag): ?>
                        <option value="<?= $tag ?>"><?= $tag ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Submit</button>
        </form>
    </body>
</html>

<?php endif; ?>
