New logic so it crushes multiple files into one
===============================================

1. Each file needed registers
2. When crunch is called registered files are crushed into a single identifier
3. Identifier is checked to see if it exists in the file
4. If the identifier isnt in the list then crunch them all
5. Store data so we know how each identifier was crushed and what from
