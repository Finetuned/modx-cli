#!/bin/bash

_modx_completion()
{
    local cur prev opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"

    # Get the list of available commands
    if [ "$prev" = "modx" ]; then
        opts=$(modx list --raw | awk '{print $1}')
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi

    # Handle command-specific options
    case "${prev}" in
        system:info)
            return 0
            ;;
        system:clearcache)
            return 0
            ;;
        resource:getlist)
            opts="--parent --context --published --hidemenu"
            COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
            return 0
            ;;
        version)
            return 0
            ;;
        *)
            ;;
    esac

    # Handle option values
    case "${prev}" in
        --parent)
            # Could suggest parent IDs here, but that would require a call to MODX
            return 0
            ;;
        --context)
            # Could suggest context keys here, but that would require a call to MODX
            return 0
            ;;
        --published|--hidemenu)
            opts="0 1"
            COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
            return 0
            ;;
        *)
            ;;
    esac

    # Handle global options
    if [[ ${cur} == -* ]]; then
        opts="--help --quiet --verbose --version --ansi --no-ansi --no-interaction --site"
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi

    return 0
}

complete -F _modx_completion modx
