<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Enregistre une activité dans la base de données
     *
     * @param string $type Type d'action (create, update, delete, quote, intervention)
     * @param mixed $model Le modèle concerné
     * @param string $description Description de l'action
     * @return void
     */
    public static function log($type, $model, $description = null)
    {
        // Générer une description par défaut si non fournie
        if (!$description) {
            $modelName = class_basename($model);
            $description = match($type) {
                'create' => "Création d'un(e) $modelName #" . $model->id,
                'update' => "Modification du/de la $modelName #" . $model->id,
                'delete' => "Suppression du/de la $modelName #" . $model->id,
                'quote' => "Nouveau devis #" . $model->id,
                'intervention' => "Intervention #" . $model->id . " mise à jour",
                default => ucfirst($type) . " $modelName #" . $model->id
            };
        }

        Activity::create([
            'type' => $type,
            'description' => $description,
            'user_id' => Auth::id(),
            'model_type' => get_class($model),
            'model_id' => $model->id
        ]);
    }
}
