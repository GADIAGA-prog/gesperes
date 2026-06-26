<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Digital Asset Links — associe l'application Android (TWA) au site afin de
 * supprimer la barre d'adresse du navigateur dans l'app publiée sur le Play
 * Store. Servi sur /.well-known/assetlinks.json.
 *
 * Renseignez l'empreinte de signature dans .env (ANDROID_SHA256_FINGERPRINT,
 * plusieurs valeurs possibles séparées par des virgules : clé d'upload + clé
 * « App Signing » du Play Console). Sans empreinte, on renvoie un tableau vide
 * (JSON valide), l'association n'est simplement pas encore active.
 */
class AssetLinksController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $package = config('gesperes.android.package');
        $empreintes = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('gesperes.android.sha256_fingerprint'))
        )));

        if (empty($empreintes)) {
            return response()->json([]);
        }

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace'                => 'android_app',
                'package_name'             => $package,
                'sha256_cert_fingerprints' => $empreintes,
            ],
        ]]);
    }
}
