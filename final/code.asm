	SECTION .text 
GLOBAL foo 
foo: 
 	push ebp 
 	mov ebp,esp
 	push ebx
	pop ebx 
 	pop ebp 
 	ret
	pop ebx 
 	pop ebp 
 	ret
