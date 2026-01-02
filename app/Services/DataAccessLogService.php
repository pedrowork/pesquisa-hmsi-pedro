<?php

namespace App\Services;

use App\Helpers\DataMaskingHelper;
use App\Models\DataAccessLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataAccessLogService
{
    /**
     * Registra acesso a dados sensíveis.
     */
    public function logDataAccess(
        string $action,
        ?Model $model = null,
        ?array $accessedFields = null,
        ?array $changes = null,
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        $user = Auth::user();
        $request = $request ?? request();

        // Mascarar campos sensíveis antes de salvar
        $maskedFields = $accessedFields ? DataMaskingHelper::maskSensitiveData($accessedFields) : null;
        $maskedChanges = $changes ? DataMaskingHelper::maskSensitiveData($changes) : null;

        return DataAccessLog::create([
            'user_id' => $user?->id,
            'model_type' => $model ? get_class($model) : 'unknown',
            'model_id' => $model?->id,
            'action' => $action, // 'view', 'create', 'update', 'delete', 'export'
            'accessed_fields' => $maskedFields,
            'changes' => $maskedChanges,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Registra visualização de dados sensíveis.
     */
    public function logView(
        Model $model,
        array $fields = [],
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        // Extrair valores dos campos acessados
        $accessedFields = [];
        foreach ($fields as $field) {
            if ($model->isFillable($field) || $model->hasAttribute($field)) {
                $accessedFields[$field] = $model->getAttribute($field);
            }
        }

        return $this->logDataAccess('view', $model, $accessedFields, null, $description, $request);
    }

    /**
     * Registra criação de dados sensíveis.
     */
    public function logCreate(
        Model $model,
        array $data,
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        return $this->logDataAccess('create', $model, null, $data, $description, $request);
    }

    /**
     * Registra atualização de dados sensíveis.
     */
    public function logUpdate(
        Model $model,
        array $oldData,
        array $newData,
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        $changes = [
            'old' => $oldData,
            'new' => $newData,
        ];

        return $this->logDataAccess('update', $model, null, $changes, $description, $request);
    }

    /**
     * Registra exclusão de dados sensíveis.
     */
    public function logDelete(
        Model $model,
        array $deletedData = [],
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        return $this->logDataAccess('delete', $model, $deletedData, null, $description, $request);
    }

    /**
     * Registra exportação de dados sensíveis.
     */
    public function logExport(
        string $modelType,
        array $exportedData,
        ?string $description = null,
        ?Request $request = null
    ): DataAccessLog {
        return $this->logDataAccess('export', null, null, ['exported' => $exportedData], $description, $request);
    }

    /**
     * Retorna logs de acesso a dados sensíveis.
     */
    public function getAccessLogs(
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $action = null,
        int $limit = 100
    ) {
        $query = DataAccessLog::query()
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($modelType) {
            $query->where('model_type', $modelType);
        }

        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        return $query->limit($limit)->get();
    }
}
