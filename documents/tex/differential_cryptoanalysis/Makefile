ARTICLE=dc_des_lr
ARTICLE_SRC=$(ARTICLE).tex des.c
SLIDES=slides
SLIDES_SRC=$(SLIDES).tex

all: article

article: $(ARTICLE).pdf
slides:  $(SLIDES).pdf

$(ARTICLE).pdf: $(ARTICLE_SRC)
	latex $(ARTICLE).tex
	latex $(ARTICLE).tex
	dvipdfmx $(ARTICLE).dvi

$(SLIDES).pdf: $(SLIDES_SRC)
	pdflatex $(SLIDES).tex
	bibtex $(SLIDES)
	pdflatex $(SLIDES).tex
	pdflatex $(SLIDES).tex

clean:
	rm -f *.aux *.toc *.lon *.lor *.lof *.ilg *.idx *.ind *.out *.log *.exa
	rm -f *.nav *.snm *.bbl *.blg

distclean: clean
	rm -f *.pdf *.dvi *~

