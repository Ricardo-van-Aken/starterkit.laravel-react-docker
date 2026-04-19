# Match headers: #6 [app runtime-base 1/9] ... OR => [app ...]
/(^#[0-9]+ \[|^ => \[)/ {
    # Deduplicate exact header lines
    if (seen[$0]++) next;

    # Identify the ID
    id = "";
    if ($1 ~ /^#[0-9]+$/) id = $1;

    # Extract [service stage ...]
    if (match($0, /\[[^\]]+\]/)) {
        full_title = substr($0, RSTART, RLENGTH);
        # Clean title: keep first two words, ignore step counts/internal
        header = full_title;
        gsub(/[\[\]]/, "", header);
        n = split(header, parts, " ");
        title = "[" parts[1] (n >= 2 && parts[2] !~ /^[0-9]+\/[0-9]+$/ && parts[2] != "internal" ? " " parts[2] : "") "]";
        
        if (!titles[title]) {
            order[count++] = title;
            titles[title] = 1;
        }
        # Link ID to this title for follow-up lines
        if (id != "") id_to_title[id] = title;
        
        # Append header to its group
        groups[title] = groups[title] $0 "\n";
    }
    next;
}
# Match follow-up lines for a stage (starts with #ID)
/^#[0-9]+ / {
    # Deduplicate identical follow-up lines
    if (seen[$0]++) next;

    if (id_to_title[$1]) {
        groups[id_to_title[$1]] = groups[id_to_title[$1]] "    " $0 "\n";
    }
    next;
}
# Fallback for other lines (like errors or general info)
{
    if ($0 ~ /^[[:space:]]*$/) next;
    if (seen[$0]++) next;
    groups["[General]"] = groups["[General]"] $0 "\n";
}
END {
    for (i = 0; i < count; i++) {
        t = order[i];
        print "\n" t;
        print groups[t];
    }
    if (groups["[General]"]) {
        print "\n[General]";
        print groups["[General]"];
    }
}
