ARTICLE=proposal
SRC=proposal.tex

all: $(ARTICLE).pdf

$(ARTICLE).pdf: $(SRC)
	latex $(ARTICLE).tex
	bibtex $(ARTICLE)
	latex $(ARTICLE).tex
	latex $(ARTICLE).tex
	dvipdfmx $(ARTICLE).dvi

clean:
	rm -f *.aux *.toc *.lon *.lor *.lof *.ilg *.idx *.ind *.out *.log *.exa

distclean: clean
	rm -f *.pdf *.dvi *~ *.bbl *.blg 

	

