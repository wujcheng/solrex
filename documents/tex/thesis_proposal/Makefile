ARTICLE=proposal
ARTICLE_SRC=$(ARTICLE).tex IEEEfull.bib IEEEtran.bst utf8.cpx
SLIDES=slides
SLIDES_SRC=$(SLIDES).tex IEEEfull.bib IEEEtran.bst utf8.cpx

all: $(ARTICLE).pdf $(SLIDES).pdf

$(ARTICLE).pdf: $(ARTICLE_SRC)
	latex $(ARTICLE).tex
	bibtex $(ARTICLE)
	latex $(ARTICLE).tex
	latex $(ARTICLE).tex
	dvipdfmx $(ARTICLE).dvi

$(SLIDES).pdf: $(SLIDES_SRC)
	pdflatex $(SLIDES).tex

clean:
	rm -f *.aux *.toc *.lon *.lor *.lof *.ilg *.idx *.ind *.out *.log *.exa
	rm -f *.nav *.snm

distclean: clean
	rm -f *.pdf *.dvi *~ *.bbl *.blg 

