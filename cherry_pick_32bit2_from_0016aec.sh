#!/usr/bin/env bash

# Cherry-pick automatico da 32bit-2 a partire da un commit specifico (incluso),
# con risoluzione conflitti automatica (ours/theirs) basata sulla data dell'ultimo
# commit del file su target vs sorgente.
#
# Uso tipico:
#   git switch <tuo-branch-target>
#   ./cherry_pick_32bit2_from_0016aec.sh
#
# Override variabili:
#   SOURCE_BRANCH=32bit-2 START_COMMIT=<hash> TARGET_BRANCH=<branch> ./cherry_pick_32bit2_from_0016aec.sh

set -euo pipefail

# Forza output Git in inglese (utile per parsing messaggi "empty"/error)
export LC_ALL=C
export LANG=C

SOURCE_BRANCH="${SOURCE_BRANCH:-32bit-2}"
START_COMMIT="${START_COMMIT:-0016aec97869e5a12ff70b4af943f6ce1bd7321b}"
TARGET_BRANCH="${TARGET_BRANCH:-$(git branch --show-current 2>/dev/null || true)}"
MAX_ITERATIONS="${MAX_ITERATIONS:-2000}"

if [ -z "${TARGET_BRANCH}" ]; then
  echo "Errore: branch target non rilevato (forse sei in detached HEAD). Imposta TARGET_BRANCH o fai checkout di un branch." >&2
  exit 1
fi

if ! git cat-file -e "${START_COMMIT}^{commit}" 2>/dev/null; then
  echo "Errore: START_COMMIT non valido o non trovato: ${START_COMMIT}" >&2
  exit 1
fi

if ! git show-ref --verify --quiet "refs/heads/${SOURCE_BRANCH}" && ! git show-ref --verify --quiet "refs/remotes/origin/${SOURCE_BRANCH}"; then
  # Prova comunque come rev (tag/sha), ma avvisa.
  if ! git cat-file -e "${SOURCE_BRANCH}^{commit}" 2>/dev/null; then
    echo "Errore: SOURCE_BRANCH non trovato: ${SOURCE_BRANCH}" >&2
    exit 1
  fi
fi

if ! git merge-base --is-ancestor "${START_COMMIT}" "${SOURCE_BRANCH}" 2>/dev/null; then
  echo "Errore: START_COMMIT ${START_COMMIT} non è un antenato di ${SOURCE_BRANCH}." >&2
  exit 1
fi

is_cherry_pick_in_progress() {
  git rev-parse -q --verify CHERRY_PICK_HEAD >/dev/null 2>&1
}

list_dirty_files() {
  # Ritorna elenco file sporchi (modificati/staged/untracked), uno per riga
  git status --porcelain 2>/dev/null | awk '{print $2}' | sed '/^$/d' || true
}

amend_if_only_known_noise() {
  # Se ci sono SOLO file "rumorosi" modificati (cache), li includiamo con amend
  # per tenere pulito il worktree tra cherry-pick.
  local dirty
  dirty=$(list_dirty_files)
  if [ -z "${dirty}" ]; then
    return 0
  fi

  # Filtra file consentiti come rumore (aggiungi qui se serve)
  local allowed_regex
  allowed_regex='^(\.php-cs-fixer\.cache|\.phpunit\.result\.cache)$'

  local f
  while IFS= read -r f; do
    # ignora lo script stesso se non tracciato
    if [[ "${f}" == "cherry_pick_32bit2_from_0016aec.sh" || "${f}" == "cherry_pick_auto.sh" ]]; then
      continue
    fi
    if ! [[ "${f}" =~ ${allowed_regex} ]]; then
      return 1
    fi
  done <<< "${dirty}"

  git add -A >/dev/null 2>&1 || true
  # Amend dell'ultimo commit, senza rilanciare hook
  git commit --amend --no-edit --no-verify >/dev/null 2>&1 || true
  return 0
}

resolve_conflicts() {
  local conflicted
  conflicted=$(git diff --name-only --diff-filter=U 2>/dev/null || true)
  if [ -z "${conflicted}" ]; then
    return 0
  fi

  local file
  for file in ${conflicted}; do
    # Ultimo commit del file su target e sorgente (epoch). Se non esiste, lascia vuoto.
    local target_date source_date
    target_date=$(git log "${TARGET_BRANCH}" --format=%ct -n 1 -- "${file}" 2>/dev/null || true)
    source_date=$(git log "${SOURCE_BRANCH}" --format=%ct -n 1 -- "${file}" 2>/dev/null || true)

    # Se il sorgente è più recente del target, prendi theirs (cioè la patch cherry-pickata).
    # Altrimenti prendi ours.
    if [ -z "${target_date}" ]; then
      git checkout --theirs "${file}" 2>/dev/null || true
    elif [ -z "${source_date}" ]; then
      git checkout --ours "${file}" 2>/dev/null || true
    elif [ "${source_date}" -gt "${target_date}" ]; then
      git checkout --theirs "${file}" 2>/dev/null || true
    else
      git checkout --ours "${file}" 2>/dev/null || true
    fi

    git add "${file}" 2>/dev/null || true
  done
}

# Costruisci lista commit (inclusivo) START_COMMIT..SOURCE_BRANCH
mapfile -t COMMITS < <(git rev-list --reverse "${START_COMMIT}^..${SOURCE_BRANCH}" 2>/dev/null || true)
if [ "${#COMMITS[@]}" -eq 0 ]; then
  # Fallback: se START_COMMIT non ha parent, includi START_COMMIT manualmente.
  mapfile -t REST < <(git rev-list --reverse "${START_COMMIT}..${SOURCE_BRANCH}" 2>/dev/null || true)
  COMMITS=("${START_COMMIT}" "${REST[@]}")
fi

TOTAL=${#COMMITS[@]}
if [ "${TOTAL}" -eq 0 ]; then
  echo "Nessun commit da cherry-pickare (range vuoto)." >&2
  exit 0
fi

echo "Target: ${TARGET_BRANCH}"
echo "Sorgente: ${SOURCE_BRANCH}"
echo "Da: ${START_COMMIT} (incluso)"
echo "Commit da applicare: ${TOTAL}"

actions=0

for commit in "${COMMITS[@]}"; do
  actions=$((actions + 1))
  if [ "${actions}" -gt "${MAX_ITERATIONS}" ]; then
    echo "Raggiunto MAX_ITERATIONS=${MAX_ITERATIONS}. Interrompo." >&2
    exit 2
  fi

  echo "[${actions}/${TOTAL}] cherry-pick ${commit:0:7}..."

  # Se tra un commit e l'altro qualche hook ha sporcato la repo (es. cache),
  # prova ad includere solo i file "rumorosi" con amend prima di continuare.
  if ! amend_if_only_known_noise; then
    echo "[${actions}/${TOTAL}] ✗ worktree sporco con file non previsti; interrompo per sicurezza." >&2
    git status --porcelain >&2 || true
    exit 4
  fi

  # Prova a cherry-pickare il commit.
  set +e
  output=$(git cherry-pick -x "${commit}" 2>&1)
  status=$?
  set -e

  if [ "${status}" -eq 0 ]; then
    # Se dopo il cherry-pick qualche hook ha modificato cache, includila nel commit appena creato.
    amend_if_only_known_noise || true
    echo "[${actions}/${TOTAL}] ✓ ${commit:0:7}"
    continue
  fi

  # Gestione casi "empty" (già applicato / nessuna modifica)
  if echo "${output}" | grep -qiE "previous cherry-pick is now empty|the previous cherry-pick is now empty|cherry-pick is now empty|nothing to commit|patch is empty"; then
    echo "[${actions}/${TOTAL}] ↷ empty (probabilmente già applicato): skip"
    if is_cherry_pick_in_progress; then
      git cherry-pick --skip >/dev/null 2>&1 || true
    fi
    continue
  fi

  # Se siamo qui, probabilmente conflitti o errori.
  echo "[${actions}/${TOTAL}] ! problema su ${commit:0:7} (tentativo risoluzione conflitti)"

  # Risolvi conflitti e prova a continuare.
  resolve_conflicts

  if is_cherry_pick_in_progress; then
    set +e
    cont_out=$(git cherry-pick --continue --no-edit 2>&1)
    cont_status=$?
    set -e

    if [ "${cont_status}" -eq 0 ]; then
      amend_if_only_known_noise || true
      echo "[${actions}/${TOTAL}] ✓ ${commit:0:7} (continue)"
      continue
    fi

    # Fallback: committa manualmente mantenendo il messaggio originale e aggiungendo trailer.
    cherry_head=$(git rev-parse CHERRY_PICK_HEAD 2>/dev/null || true)
    if [ -n "${cherry_head}" ]; then
      commit_msg=$(git log -1 --format=%B "${cherry_head}" 2>/dev/null || true)
      resolve_conflicts
      git add -A >/dev/null 2>&1 || true
      git commit --no-verify -m "${commit_msg}

(cherry picked from commit ${cherry_head})" >/dev/null 2>&1 || true
      # Chiudi eventuale sequencer rimasto aperto
      git cherry-pick --continue --no-edit >/dev/null 2>&1 || true
      amend_if_only_known_noise || true
      echo "[${actions}/${TOTAL}] ✓ ${commit:0:7} (manual commit)"
      continue
    fi
  fi

  echo "[${actions}/${TOTAL}] ✗ impossibile applicare ${commit:0:7}. Output:" >&2
  echo "${output}" >&2
  exit 3

done

echo "Cherry-pick completato: ${actions}/${TOTAL} commit processati."