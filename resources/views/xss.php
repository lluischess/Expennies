<?php


// Evitar ataques XSS en php sin twig o blade
echo htmlspecialchars($username, ENT_QUOTES,'UTF-8');