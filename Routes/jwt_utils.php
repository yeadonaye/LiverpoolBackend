<?php

    // URL de l'API d'authentification — seule responsable de la vérification des tokens
    // J'ai aussi enleve la cle secrete, tt les verifs du token doivent ce faire dans l'api d'auth
    define('AUTH_API_URL', 'https://apiliverpool.alwaysdata.net/authapi.php');


    function verify_token_via_auth_api(?string $jwt): ?array {
        if (!$jwt) return null;

        $ch = curl_init(AUTH_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $jwt,
            ],
            CURLOPT_TIMEOUT => 5,
        ]);

        $response   = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatus !== 200) return null;

        $decoded = json_decode($response, true);
        if (!$decoded || ($decoded['status_code'] ?? 0) !== 200) return null;

        return $decoded['data'] ?? null; // ['login' => ..., 'role' => ...]
    }

    function check_auth(?string $jwt): array {
        $payload = verify_token_via_auth_api($jwt);
        if (!$payload) {
            deliver_response(401, "Unauthorized", "Votre token n'est pas valide ou a expiré.");
            exit();
        }
        return $payload; // retourne ['login', 'role'] au cas ou on aurais besoin
    }

// Vérifie si c'est un coach
    function check_coach(?string $jwt): array {
        $payload = check_auth($jwt);
        if (($payload['role'] ?? '') !== 'coach') {
            deliver_response(403, "Forbidden", "Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.");
            exit();
        }
        return $payload;
    }

// Vérifie si c'est un joueur
    function check_joueur(?string $jwt): array {
        $payload = check_auth($jwt);
        if (($payload['role'] ?? '') !== 'joueur') {
            deliver_response(403, "Forbidden", "Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.");
            exit();
        }
        return $payload;
    }

// Méthodes générales, fournis par les profs en TP
    function get_authorization_header(): ?string {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER['Authorization']);
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                return trim($requestHeaders['Authorization']);
            }
        }
        return null;
    }

    function get_bearer_token(): ?string {
        $headers = get_authorization_header();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return ($matches[1] === 'null') ? null : $matches[1];
        }
        return null;
    }

    function deliver_response(int $status_code, string $status_message, $data = null): void {
        http_response_code($status_code);
        header("Content-Type: application/json; charset=utf-8");
        $response = [
            'status_code'    => $status_code,
            'status_message' => $status_message,
            'data'           => $data,
        ];
        $json = json_encode($response);
        if ($json === false) die('json encode ERROR : ' . json_last_error_msg());
        echo $json;
        die();
    }

?>