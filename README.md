RamseyCompiler
==============

A compiler for the (simple) language Ramsey, for a CS355 project.

These notes are based on our description of the language in class. This should suffice for now, but eventually we'll have to produce a full grammar to accurately describe the language.

short and long integers, boolean types (as "small", "big", "boo") with explicit declaration
integer operations: +, -, *, /, "mod"
assignment operator: "<\-", implicit upcasting (small to big), explicit downcasting (big to small) indicated as "chop (<expression>)"
conditional: "if (COND)", "elf (COND)", "else", "endif"
iterate: "while (COND)", "endwhile"
function: "fun NAME (PARAMS) as TYPE", "toss IDENT" (return), "endfun"
logical operators: "and", "or", "not"
comparison operators: "<", ">", "<=", ">=", "=", "!="
comments: "#...\n"

identifiers start with letter, can contain letters, digits, underscore
all keywords are lowercase

a Ramsey source file will contain only functions and will be compiled to a *.s, then linked with a C source file that calls the functions from its main function

Extra (optional) features:
- arrays
- input / output
- floating-point numbers
