# Grammar 

Backus–Naur form (BNF) notation

Non-Terminal | ::= | Elements
--- | --- | --- 
query | | conditional_expression (order_by_expression)?
order_by_expression | | "ORDER" "BY" field ("ASC" &vert; "DESC")
conditional_expression | | conditional_term &vert; conditional_expression OR conditional_term
conditional_term | | conditional_factor &vert; conditional_term AND conditional_factor
conditional_factor | | ( "!" &vert; "NOT" )? conditional_primary
conditional_primary | | simple_cond_expression &vert; "(" conditional_expression ")"
simple_cond_expression | | comparison_expression &vert; contains_expression
comparison_expression | | field comparison_operator primary
contains_expression | | field contains_operator primary
comparison_operator | | = &vert; != &vert; \> &vert; >= &vert; < &vert; <= &vert; 
contains_operator | | ~ 
primary | | NUMBER &vert; "-" NUMBER &vert; STRING &vert; "true" &vert; "false"
field | | STRING