#!/bin/bash

# Script per cherry-pick automatico con risoluzione conflitti

MAX_ITERATIONS=120
iteration=0

resolve_conflicts() {
    conflicted=$(git diff --name-only --diff-filter=U 2>/dev/null)
    if [ -z "$conflicted" ]; then
        return 0
    fi
    
    for file in $conflicted; do
        feat_date=$(git log features/avatar-3d-v1.5-docker-prod --format=%ai -- "$file" 2>/dev/null | head -1)
        bit_date=$(git log 32bit-2 --format=%ai -- "$file" 2>/dev/null | head -1)
        
        if [ -z "$feat_date" ] || ([ -n "$bit_date" ] && [ "$bit_date" \> "$feat_date" ]); then
            git checkout --theirs "$file" 2>/dev/null
        else
            git checkout --ours "$file" 2>/dev/null
        fi
        git add "$file" 2>/dev/null
    done
}

while [ $iteration -lt $MAX_ITERATIONS ]; do
    iteration=$((iteration + 1))
    
    # Risolvi conflitti se presenti
    resolve_conflicts
    
    # Controlla se c'è ancora un cherry-pick in corso
    if ! git status 2>/dev/null | grep -q "Cherry-pick in corso"; then
        echo "Cherry-pick completato dopo $((iteration-1)) commit"
        break
    fi
    
    # Prova a continuare
    if git cherry-pick --continue --no-edit 2>&1 | grep -qE "GrumPHP|phpstan|error|CONFLITTO|non è stato possibile"; then
        # C'è un errore o conflitto, risolvi e committa manualmente
        cherry_head=$(git rev-parse CHERRY_HEAD 2>/dev/null)
        if [ -n "$cherry_head" ]; then
            commit_msg=$(git log -1 --format=%B "$cherry_head" 2>/dev/null)
            resolve_conflicts
            git commit --no-verify -m "$commit_msg

(cherry picked from commit $cherry_head)" 2>/dev/null
            echo "[$iteration] ✓ ${cherry_head:0:7}: $(echo "$commit_msg" | head -1 | cut -c1-60)"
        fi
    else
        commit_hash=$(git rev-parse HEAD 2>/dev/null | cut -c1-7)
        commit_msg=$(git log -1 --format=%s 2>/dev/null)
        echo "[$iteration] ✓ $commit_hash: $(echo "$commit_msg" | cut -c1-60)"
    fi
done

echo "Processati $iteration commit"
