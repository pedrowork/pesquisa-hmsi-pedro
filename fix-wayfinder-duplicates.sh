#!/bin/sh
# Script para remover duplicações geradas pelo wayfinder

FILE="resources/js/routes/user-password/index.ts"

if [ ! -f "$FILE" ]; then
    echo "Arquivo $FILE não encontrado, pulando..."
    exit 0
fi

# Contar quantas vezes 'export const update' aparece
COUNT=$(grep -c "export const update" "$FILE" || echo "0")

if [ "$COUNT" -le 1 ]; then
    echo "Nenhuma duplicação encontrada em $FILE"
    exit 0
fi

echo "⚠️  Encontradas $COUNT ocorrências de 'export const update' em $FILE"
echo "Removendo duplicações..."

# Criar arquivo temporário mantendo apenas a primeira ocorrência
awk '
/export const update/ {
    if (!found) {
        found = 1
        print
        next
    }
    skip = 1
    next
}
skip && /^export / {
    skip = 0
}
!skip {
    print
}
' "$FILE" > "$FILE.tmp"

# Substituir arquivo original
mv "$FILE.tmp" "$FILE"

echo "✅ Duplicações removidas com sucesso!"
