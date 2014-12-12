; Dwayne Towell
;
; CS 220
;
; starting point for f(x,y) function


	SECTION .text
	
GLOBAL f
f:
	; prolog
	push ebp		; save old base pointer
	mov	 ebp,esp	; setup new base pointer
	push ebx		; preserve EBX for caller

	; [ebp+8]  - x (arg1)
	; [ebp+12] - y (arg2)

    ; ------------------------------------------
	; perform actual function calculation here
	
	mov	 ebx, [ebp+8] 
	mov  eax, [ebp+12]
	inc  ebx
	mov  ecx, ebx
	mov  edx, ebx
	add  ebx, ecx
	add  ebx, edx
	sub  eax, ebx
	
	
    ; ------------------------------------------

	; %eax = return value fib(n)

	; epilog 
	pop  ebx		; restore EBX for caller
	pop  ebp		; restore old base pointer
	ret